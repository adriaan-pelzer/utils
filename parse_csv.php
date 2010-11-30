<?php

/* This have to be adapted before it can be used - copied directly from non-portable application */

if (isset ($_POST['s3_csv_submit'])) {
    if ($_FILES['s3_csv']['error'] > 0) {
        $error['s3_csv'] = $_FILES['s3_csv']['error'];
    } else {
        if (!($csv = file_get_contents ($_FILES['s3_csv']['tmp_name']))) {
            $error['s3_csv'] = "CSV bestand kun niet gelees worden";
        } else {
            $csv_array = array();
            $fail = TRUE;

            foreach (explode ("\n", $csv) as $linestr) {
                if ($_POST['s3_csv_option'] == 'comma') {
                    $line = explode (",", $linestr);
                } else {
                    $line = explode ("\t", $linestr);
                }
                if (sizeof ($line) > 1) {
                    array_push ($csv_array, $line);
                    $fail = FALSE;
                }
            }

            if ($fail) {
                $error['file'] = "Kies een geldig CSV bestand aub";
            }
        }

        if (sizeof ($error) == 0) {
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
                        /*echo "State: ".$state."<br />";
                        echo "element: ".$csv_array[$i][$j]."<br />";
                        echo "k: ".$k."<br />";*/
                    if ($state == NULL) {
                        if (preg_match ('/^\"/', $csv_array[$i][$j])) {
                            if (!preg_match ('/^\".*\"$/', $csv_array[$i][$j])) {
                                $state = 'doublestart';
                            }
                            $csv_a[$i-$offset][$k] = preg_replace ('/^\"/', '', $csv_array[$i][$j]);
                            if (preg_match ('/^\".*\"$/', $csv_array[$i][$j])) {
                                $csv_a[$i-$offset][$k] = preg_replace ('/\"$/', '', $csv_a[$i-$offset][$k]);
                                $k++;
                            }
                        } else if ($quotes = preg_match ("/^\'/", $csv_array[$i][$j])) {
                            if (!preg_match ('/^\'.*\'$/', $csv_array[$i][$j])) {
                                $state = 'singlestart';
                            }
                            $csv_a[$i-$offset][$k] = preg_replace ("/^\'/", "", $csv_array[$i][$j]);
                            if (preg_match ('/^\'.*\'$/', $csv_array[$i][$j])) {
                                $csv_a[$i-$offset][$k] = preg_replace ('/\'$/', '', $csv_a[$i-$offset][$k]);
                                $k++;
                            }
                        } else {
                            $csv_a[$i-$offset][$k] = $csv_array[$i][$j];
                            $k++;
                        }
                    } else {
                        if ((preg_match ('/\"$/', $csv_array[$i][$j])) && ($state == 'doublestart')) {
                            $state = NULL;
                            $csv_a[$i-$offset][$k] .= ",".preg_replace ('/\"$/', '', $csv_array[$i][$j]);
                            $k++;
                        } else if ((preg_match ("/\'$/", $csv_array[$i][$j])) && ($state == 'singlestart')) {
                            $state = NULL;
                            $csv_a[$i-$offset][$k] .= ",".preg_replace ("/\'$/", "", $csv_array[$i][$j]);
                            $k++;
                        } else {
                            $csv_a[$i-$offset][$k] .= ",".$csv_array[$i][$j];
                        }
                    }
                }
            }

            foreach ($csv_a as $recipient) {
                $i = 0;

                $found = FALSE;

                for ($i = 1; $i < min (sizeof ($recipient), 20); $i++) {
                    if (preg_match ('/[^@]+@[^@]+\.[^@]+/', $recipient[$i])) {
                        $found = TRUE;
                    }
                }

                $i = 0;

                if ($found) {
                    foreach ($recipient as $field) {
                        if (preg_match ("/[^@]+@[^@]+\.[^@]+/", $field)) {
                                /*if ($i == 0) {
                                    if (!(preg_match ("/[^@]+@[^@]+\.[^@]+/", $recipient[1]))) {
                                        print_r ($recipient);
                                        $error['file'] = "Please make the first field a name field, not an email field";
                                        break;
                                    }
                                } else */if ($i == 1) {
                                    array_push ($recipients, array ('name'=>$recipient[0], 'email'=>$field));
                                    break;
                                } else {
                                    array_push ($recipients, array ('name'=>$recipient[0]." ".$recipient[1], 'email'=>$field));
                                    break;
                                }
                        }

                        $i++;
                    }
                }

                if (isset ($error['file'])) {
                    break;
                }
            }

            if (sizeof ($error) == 0) {
                $wpdb->query ("DELETE FROM `".$wpdb->prefix."recipients` WHERE `trans_index`='".$_SESSION['session_id']."'");

                foreach ($recipients as $recipient) {
                    if (!($wpdb->insert ($wpdb->prefix."recipients", array ("name"=>$recipient['name'], "email"=>$recipient['email'], "trans_index"=>$_SESSION['session_id'])))) {
                        $error['global'] = "Cannot start a new transaction - please contact the site administrator";
                    }
                }
            }
        }
    }

    if (sizeof ($error) > 0) {
        $framestate = "3a";
    } else {
        $framestate = "3b";
    }
}

/*if (isset ($_POST['s3_csv_submit'])) {
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
}*/
?>
