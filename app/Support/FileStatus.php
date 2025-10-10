<?php // app/Support/FileStatus.php
namespace App\Support;

final class FileStatus {
  const DRAFT='draft'; const SUBMITTED='submitted'; const UNDER_REVIEW='under_review';
  const APPROVED='approved'; const REJECTED='rejected'; const ARCHIVED='archived';

  public static function all(): array { return [self::DRAFT,self::SUBMITTED,self::UNDER_REVIEW,self::APPROVED,self::REJECTED,self::ARCHIVED]; }
}
