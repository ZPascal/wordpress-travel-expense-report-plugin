<?php
/**
 * Unit Tests for Travel Expense Report Plugin Helper Functions
 *
 * Tests cover:
 * - ter_receipt_basename_from_value()
 * - ter_get_temp_upload_dir()
 * - ter_resolve_uploaded_file_path()
 *
 * Compatible with PHP 8.1 and PHPUnit 9.5+
 */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase {

    /**
     * Test ter_receipt_basename_from_value with URL containing query parameter
     */
    public function testTerReceiptBasenameFromValue_url_query_file(): void {
        $url = 'http://example.org/wp-admin/admin-ajax.php?action=ter_serve_temp_file&file=abcd1234.pdf&nonce=test';
        $this->assertEquals('abcd1234.pdf', ter_receipt_basename_from_value($url));
    }

    /**
     * Test ter_receipt_basename_from_value with URL path
     */
    public function testTerReceiptBasenameFromValue_url_path(): void {
        $url = 'http://example.org/tmp/travel-expense-report/receipt-1.png';
        $this->assertEquals('receipt-1.png', ter_receipt_basename_from_value($url));
    }

    /**
     * Test ter_receipt_basename_from_value with simple filename
     */
    public function testTerReceiptBasenameFromValue_filename(): void {
        $this->assertEquals('file.pdf', ter_receipt_basename_from_value('file.pdf'));
    }

    /**
     * Test ter_receipt_basename_from_value with empty string
     */
    public function testTerReceiptBasenameFromValue_empty(): void {
        $this->assertEquals('', ter_receipt_basename_from_value(''));
    }

    /**
     * Test ter_get_temp_upload_dir creates and returns path with correct suffix
     */
    public function testGetTempUploadDir_creates_and_returns_path(): void {
        $dir = ter_get_temp_upload_dir(true);
        $this->assertNotEmpty($dir);
        $this->assertStringEndsWith('travel-expense-report/', $dir);
        $this->assertDirectoryExists($dir);
        // Cleanup: remove if empty
        if (is_dir($dir)) {
            @rmdir($dir);
        }
    }

    /**
     * Test ter_resolve_uploaded_file_path validates file is within temp dir
     */
    public function testResolveUploadedFilePath_security(): void {
        $dir = ter_get_temp_upload_dir(true);
        $testFile = $dir . 'testfile.txt';

        // Create test file
        file_put_contents($testFile, 'test content');
        $basename = basename($testFile);

        // Resolve should return the full path
        $resolved = ter_resolve_uploaded_file_path($basename);
        $this->assertNotFalse($resolved);
        $this->assertFileExists($resolved);
        $this->assertStringContainsString('travel-expense-report', $resolved);

        // Cleanup
        unlink($testFile);
        if (is_dir($dir)) {
            @rmdir($dir);
        }
    }

    /**
     * Test ter_resolve_uploaded_file_path rejects non-existent file
     */
    public function testResolveUploadedFilePath_nonexistent(): void {
        $resolved = ter_resolve_uploaded_file_path('nonexistent_file_12345.pdf');
        $this->assertFalse($resolved);
    }

    /**
     * Test ter_resolve_uploaded_file_path rejects path traversal attempts
     */
    public function testResolveUploadedFilePath_path_traversal(): void {
        // Attempt to traverse out of temp dir
        $resolved = ter_resolve_uploaded_file_path('../../../etc/passwd');
        $this->assertFalse($resolved);
    }
}

