<?php
/**
 * Simple MO file generator from PO file
 * WordPress-compatible MO format
 */

function parse_po_file($filename) {
    $data = array();
    $handle = fopen($filename, 'r');

    if (!$handle) {
        die("Cannot open $filename\n");
    }

    $current = array('msgid' => '', 'msgstr' => '');

    while (!feof($handle)) {
        $line = fgets($handle);
        $line = trim($line);

        if ($line === '' || $line[0] === '#') {
            continue;
        }

        if (strpos($line, 'msgid ') === 0) {
            if (!empty($current['msgid'])) {
                if (!empty($current['msgstr'])) {
                    $data[$current['msgid']] = $current['msgstr'];
                }
            }
            $current['msgid'] = trim(substr($line, 6), ' "');
            $current['msgstr'] = '';
        } elseif (strpos($line, 'msgstr ') === 0) {
            $current['msgstr'] = trim(substr($line, 7), ' "');
        }
    }

    // Add last entry
    if (!empty($current['msgid']) && !empty($current['msgstr'])) {
        $data[$current['msgid']] = $current['msgstr'];
    }

    fclose($handle);
    return $data;
}

function generate_mo_file($po_file, $mo_file) {
    $translations = parse_po_file($po_file);

    printf("Parsed %d translations\n", count($translations));

    $output = '';

    // Sort by msgid for consistent binary output
    ksort($translations);

    // Collect string data
    $strings = array();
    $string_offset = 0;
    $ids = '';
    $strs = '';

    foreach ($translations as $msgid => $msgstr) {
        $id_len = strlen($msgid);
        $str_len = strlen($msgstr);

        // Collect for offset table
        $ids .= pack('II', $id_len, 0);  // Will fill offset later
        $strs .= pack('II', $str_len, 0);  // Will fill offset later

        // Collect strings
        $strings[] = array('id' => $msgid, 'str' => $msgstr);
    }

    $num = count($strings);

    // Calculate offsets
    $header_size = 7 * 4;
    $id_table_offset = $header_size;
    $str_table_offset = $id_table_offset + ($num * 8);
    $strings_offset = $str_table_offset + ($num * 8);

    // Write header
    $output .= pack('N', 0x950412de);      // Magic number (Big-endian)
    $output .= pack('N', 0);               // Version
    $output .= pack('N', $num);            // Number of strings
    $output .= pack('N', $id_table_offset);
    $output .= pack('N', $str_table_offset);
    $output .= pack('N', 0);               // Hash table size
    $output .= pack('N', 0);               // Hash table offset

    // Build ID and string tables
    $id_data = '';
    $str_data = '';
    $current_offset = $strings_offset;

    foreach ($strings as $string) {
        $id_bytes = $string['id'];
        $str_bytes = $string['str'];

        // ID table entry
        $id_data .= pack('NN', strlen($id_bytes), $current_offset);
        $current_offset += strlen($id_bytes) + 1;

        // String table entry
        $str_data .= pack('NN', strlen($str_bytes), $current_offset);
        $current_offset += strlen($str_bytes) + 1;
    }

    $output .= $id_data;
    $output .= $str_data;

    // Add strings
    foreach ($strings as $string) {
        $output .= $string['id'] . "\0";
    }
    foreach ($strings as $string) {
        $output .= $string['str'] . "\0";
    }

    // Write file
    if (file_put_contents($mo_file, $output)) {
        printf("✅ .mo file created: %s\n", $mo_file);
        printf("File size: %d bytes\n", filesize($mo_file));
        return true;
    } else {
        printf("❌ Error writing .mo file\n");
        return false;
    }
}

// Run
$script_dir = dirname(__FILE__);
$po_file = $script_dir . '/travel-expense-report-de_DE.po';
$mo_file = $script_dir . '/travel-expense-report-de_DE.mo';

if (generate_mo_file($po_file, $mo_file)) {
    exit(0);
} else {
    exit(1);
}

