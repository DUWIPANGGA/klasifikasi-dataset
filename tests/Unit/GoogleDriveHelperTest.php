<?php

namespace Tests\Unit;

use App\Models\Image;
use App\Support\GoogleDriveHelper;
use PHPUnit\Framework\TestCase;

class GoogleDriveHelperTest extends TestCase
{
    public function test_it_identifies_google_drive_urls(): void
    {
        $this->assertTrue(GoogleDriveHelper::isGoogleDriveUrl('https://drive.google.com/file/d/1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2/view?usp=drive_link'));
        $this->assertTrue(GoogleDriveHelper::isGoogleDriveUrl('https://drive.google.com/uc?export=view&id=1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2'));
        $this->assertTrue(GoogleDriveHelper::isGoogleDriveUrl('https://lh3.googleusercontent.com/d/1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2'));
        $this->assertFalse(GoogleDriveHelper::isGoogleDriveUrl('https://example.com/image.jpg'));
    }

    public function test_it_extracts_file_id_correctly(): void
    {
        $expectedId = '1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2';

        $urls = [
            'https://drive.google.com/file/d/1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2/view?usp=drive_link',
            'https://drive.google.com/file/d/1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2/view',
            'https://drive.google.com/file/d/1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2',
            'https://drive.google.com/open?id=1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2',
            'https://drive.google.com/uc?id=1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2&export=view',
            'https://drive.google.com/uc?export=view&id=1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2',
            'https://drive.google.com/thumbnail?id=1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2&sz=w1000',
            'https://docs.google.com/file/d/1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2',
            'https://lh3.googleusercontent.com/d/1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2',
        ];

        foreach ($urls as $url) {
            $this->assertEquals($expectedId, GoogleDriveHelper::extractFileId($url), "Failed extracting from $url");
        }
    }

    public function test_it_converts_to_direct_image_url(): void
    {
        $input = 'https://drive.google.com/file/d/1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2/view?usp=drive_link';
        $directUrl = GoogleDriveHelper::toDirectImageUrl($input);
        $thumbnailUrl = GoogleDriveHelper::toThumbnailUrl($input, 400);

        $this->assertEquals('https://lh3.googleusercontent.com/d/1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2', $directUrl);
        $this->assertEquals('https://lh3.googleusercontent.com/d/1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2=w400', $thumbnailUrl);
    }

    public function test_it_generates_safe_filename(): void
    {
        $input = 'https://drive.google.com/file/d/1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2/view?usp=drive_link';
        $filename = GoogleDriveHelper::getSafeFilename($input);

        $this->assertEquals('gdrive_1EP27H7JJ4ua_d6NgFMfZbrdptRxfI1b2.jpg', $filename);
    }
}
