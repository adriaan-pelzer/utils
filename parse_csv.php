<?php

if (isset ($_POST['s3_csv_submit'])) {
    if ($_FILES['s3_csv']['error'] > 0) {
        $error['s3_csv'] = $_FILES['s3_csv']['error'];
    } else {
        if (!($csv = fopen ($_FILES['s3_csv']['tmp_name'], 'r'))) {
            $error['s3_csv'] = "Cannot read csv file";
        } else {
            $csv_array = array();

            while ($line = fgetcsv ($csv, 4096)) {
                array_push ($csv_array, $line);
            }

            fclose ($csv);
        }

        $csv_a = array();
        $state = NULL;
        $offset = 0;

        for ($i = 0; $i < sizeof ($csv_array); $i++) {
            $csv_a[$i] = array();

            if ($state == NULL) {
                $k = 0;
            } else {
                $offset++;
            }

            for ($j = 0; $j < sizeof ($csv_array[$i]); $j++) {
                if ($state == NULL) {
                    if (preg_match ('/\"/', $csv_array[$i][$j])) {
                        $state = 'doublestart';
                        $csv_a[$i-$offset][$k] = preg_replace ('/\"/', '', $csv_array[$i][$j]);
                    } else if (preg_match ("/\'/", $csv_array[$i][$j])) {
                        $state = 'singlestart';
                        $csv_a[$i-$offset][$k] = preg_replace ("/\'/", "", $csv_array[$i][$j]);
                    } else {
                        $csv_a[$i-$offset][$k] = $csv_array[$i][$j];
                        $k++;
                    }
                } else {
                    if ((preg_match ('/\"/', $csv_array[$i][$j])) && ($state == 'doublestart')) {
                        $state = NULL;
                        $csv_a[$i-$offset][$k] .= ",".preg_replace ('/\"/', '', $csv_array[$i][$j]);
                        $k++;
                    } else if ((preg_match ("/\'/", $csv_array[$i][$j])) && ($state == 'singlestart')) {
                        $state = NULL;
                        $csv_a[$i-$offset][$k] .= ",".preg_replace ("/\'/", "", $csv_array[$i][$j]);
                        $k++;
                    } else {
                        $csv_a[$i-$offset][$k] .= ",".$csv_array[$i][$j];
                    }
                }
            }
        }
    }
}
?>
