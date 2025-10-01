<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FoldersTableSeeder extends Seeder
{
    public function run(): void
    {
        $divisions = DB::table('divisions')->select('id','code','name')->get();
        $superAdmin = DB::table('users')->where('email','superadmin@example.com')->first();
        $creatorId = $superAdmin?->id ?? 1;

        foreach ($divisions as $d) {
            // root per divisi
            $rootSlug = Str::slug($d->code.'-root');
            $root = DB::table('folders')->where('division_id',$d->id)->where('slug',$rootSlug)->first();

            if (!$root) {
                $rootId = DB::table('folders')->insertGetId([
                    'division_id' => $d->id,
                    'parent_id'   => null,
                    'name'        => $d->name.' Root',
                    'slug'        => $rootSlug,
                    'visibility'  => 'private',
                    'created_by'  => $creatorId,
                    'updated_by'  => $creatorId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            } else {
                $rootId = $root->id;
                DB::table('folders')->where('id',$rootId)->update([
                    'name'       => $d->name.' Root',
                    'visibility' => 'private',
                    'updated_by' => $creatorId,
                    'updated_at' => now(),
                ]);
            }

            // subfolders
            foreach ([
                ['name'=>'Draft','visibility'=>'private'],
                ['name'=>'Publik','visibility'=>'public'],
                ['name'=>'Arsip','visibility'=>'private'],
                ['name'=>'Review','visibility'=>'private'],
            ] as $s) {
                $slug = Str::slug($d->code.'-'.$s['name']);
                $exist = DB::table('folders')->where('division_id',$d->id)->where('slug',$slug)->first();

                if (!$exist) {
                    DB::table('folders')->insert([
                        'division_id' => $d->id,
                        'parent_id'   => $rootId,
                        'name'        => $s['name'],
                        'slug'        => $slug,
                        'visibility'  => $s['visibility'],
                        'created_by'  => $creatorId,
                        'updated_by'  => $creatorId,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                } else {
                    DB::table('folders')->where('id',$exist->id)->update([
                        'parent_id'  => $rootId,
                        'name'       => $s['name'],
                        'visibility' => $s['visibility'],
                        'updated_by' => $creatorId,
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
