<?php // app/Http/Controllers/FileReviewController.php
namespace App\Http\Controllers;

use App\Models\FileEntry;
use App\Models\Review;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FileReviewController extends Controller
{
    // daftar review: admin_arsip & super_admin
    public function queue(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['admin_arsip','super_admin']), 403);

        $size = (int)$request->input('size', 20);
        $q    = $request->input('q');

        $files = FileEntry::with(['division','folder','uploader'])
            ->whereIn('status',['submitted','under_review'])
            ->when($q, fn($qq)=>$qq->where(fn($w)=>$w
                ->where('title','like',"%{$q}%")
                ->orWhere('original_name','like',"%{$q}%")
            ))
            ->orderByDesc('created_at')
            ->paginate($size)
            ->withQueryString();

        // 🔹 Tambahkan variabel yang dibutuhkan oleh view
        $divisions = \App\Models\Division::all(['id','name']);
        $folders   = \App\Models\Folder::all(['id','name']);
        $statuses  = ['submitted','under_review','approved','rejected','archived'];

        return view('reviews.queue', compact('files','q','size','divisions','folders','statuses'));
    }

    // set under_review (optional step)
    public function take(Request $request, FileEntry $file)
    {
        abort_unless($request->user()->hasAnyRole(['admin_arsip','super_admin']), 403);
        if ($file->status === 'submitted') {
            $file->update(['status'=>'under_review','reviewed_by'=>$request->user()->id,'reviewed_at'=>now()]);
            ActivityLog::create([
                'subject_type'=>'files','subject_id'=>$file->id,'action'=>'under_review',
                'causer_id'=>$request->user()->id,'properties'=>['title'=>$file->title],
                'ip_address'=>$request->ip(),'user_agent'=>substr((string)$request->userAgent(),0,255),'created_at'=>now(),
            ]);
        }
        return back()->with('success','File taken for review.');
    }

    public function approve(Request $request, FileEntry $file)
    {
        abort_unless($request->user()->hasAnyRole(['admin_arsip','super_admin']), 403);
        DB::transaction(function() use ($request,$file){
            $file->update([
                'status'=>'approved',
                'reviewed_by'=>$request->user()->id,
                'reviewed_at'=>now(),
                'approved_at'=>now(),
                'review_notes'=>null,
            ]);
            Review::create([
                'file_id'=>$file->id,'reviewer_id'=>$request->user()->id,'decision'=>'approve','notes'=>null,
            ]);
            ActivityLog::create([
                'subject_type'=>'files','subject_id'=>$file->id,'action'=>'approve',
                'causer_id'=>$request->user()->id,'properties'=>['title'=>$file->title],
                'ip_address'=>$request->ip(),'user_agent'=>substr((string)$request->userAgent(),0,255),'created_at'=>now(),
            ]);
        });
        return back()->with('success','Approved.');
    }

    public function reject(Request $request, FileEntry $file)
    {
        abort_unless($request->user()->hasAnyRole(['admin_arsip','super_admin']), 403);
        $data = $request->validate(['notes'=>['required','string','max:1000']]);
        DB::transaction(function() use ($request,$file,$data){
            $file->update([
                'status'=>'rejected',
                'reviewed_by'=>$request->user()->id,
                'reviewed_at'=>now(),
                'review_notes'=>$data['notes'],
            ]);
            Review::create([
                'file_id'=>$file->id,'reviewer_id'=>$request->user()->id,'decision'=>'reject','notes'=>$data['notes'],
            ]);
            ActivityLog::create([
                'subject_type'=>'files','subject_id'=>$file->id,'action'=>'reject',
                'causer_id'=>$request->user()->id,'properties'=>['title'=>$file->title,'notes'=>$data['notes']],
                'ip_address'=>$request->ip(),'user_agent'=>substr((string)$request->userAgent(),0,255),'created_at'=>now(),
            ]);
        });
        return back()->with('success','Rejected with notes.');
    }
}
