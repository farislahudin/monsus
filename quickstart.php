<?php
require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Sheets API PHP Quickstart');
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('online');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

// mengambil nilai dari form pcl
function get_from_pcl(){
  // Get the API client and construct the service object.
  $client = getClient();
  $service = new Google_Service_Sheets($client);
  $spreadsheetId = '11i6ghiytWv23Tc1BWNKw9XGhSSya3HMT0vGGb9Hwibk';
  $range = 'Form Responses 1';
  $response = $service->spreadsheets_values->get($spreadsheetId, $range);
  $values = $response->getValues();
  $arr = array();
  return $values;
}

// mengambil nilai dari form pml
function get_from_pml(){
  // Get the API client and construct the service object.
  $client = getClient();
  $service = new Google_Service_Sheets($client);
  $spreadsheetId = '1mb9HpWvawxWB3hp6d9s23zZlIS7cum_Yqo8KQOdqobk';
  $range = 'Form Responses 1';
  $response = $service->spreadsheets_values->get($spreadsheetId, $range);
  $values = $response->getValues();
  return $values;
}

// mengambil nilai dari form editor
function get_from_editor(){
  // Get the API client and construct the service object.
  $client = getClient();
  $service = new Google_Service_Sheets($client);
  $spreadsheetId = '1i-kbLVvz2hWfyUzEVGMP6hMsEYZ48Tf-ITfUs-dP2DU';
  $range = 'Form Responses 1';
  $response = $service->spreadsheets_values->get($spreadsheetId, $range);
  $values = $response->getValues();
  return $values;
}

function get_unique_rows($rows){
	$copy = $rows;
	for($i=0; $i<count($rows); $i++){
	  $duplicate_index = array();
	  for($j=0; $j<count($copy); $j++){
		  $row1 = $rows[$i];
		  $row2 = $copy[$j];
		  $id1 = $row1[4].$row1[5].$row1[6].$row1[7].$row1[8];
		  $id2 = $row2[4].$row2[5].$row2[6].$row2[7].$row2[8];
		  if($id1 == $id2){
			  array_push($duplicate_index, $j);
		  }
	  } 
	  $duplicate_index = array_reverse($duplicate_index);
	  if(count($duplicate_index) > 1){
	  	for($k=1; $k<count($duplicate_index); $k++){
	 		array_splice($copy, $duplicate_index[$k], 1); 
		  }
	  }
  }
  return $copy;
}

function transposeData($data)
{
  $retData = array();
    foreach ($data as $row => $columns) {
      foreach ($columns as $row2 => $column2) {
          $retData[$row2][$row] = $column2;
      }
    }
  return $retData;
}

function name_array($values){
  $t = transposeData($values);
  $result = array_column($t, null, '0');
  foreach ($result as $key => $subArr) {
      unset($subArr['0']);
      $result[$key] = $subArr;
  }
  return $result;
}

//mengambil record dari form pcl berdasarkan filter wilayah -> digunakan untuk membuat grafik
//input berupa string tingkatan, nama kabupaten, kecamatan, desa, dan nks
// input tingkatan jika 1 -> pcl, 2 -> pml, 3 -> editor
//output berupa array, jika mau menghitung jumlahnya menggunakan fungsi count()
// function get_filter_wilayah($tingkatan, $kab, $kec = NULL, $desa = NULL, $nks = NULL){
//   if ($tingkatan == 1){
//     $values = get_from_pcl();
//   }
//   if ($tingkatan == 2){
//     $values = get_from_pml();
//   }
//   if ($tingkatan == 3){
//     $values = get_from_editor();
//   }
//   if(!is_null($kab)){
//     $filter_values_kab = array_filter($values, function($var) use ($kab){
//       return (strpos($var[4],$kab)!==false);
//     });
//     if(!is_null($kec)){
//       $filter_values_kec = array_filter($filter_values_kab, function($var) use ($kec){
//         return (strpos($var[5],$kec)!==false);
//       });
//       if(!is_null($desa)){
//         $filter_values_desa = array_filter($filter_values_kec, function($var) use ($desa){
//           return (strpos($var[6],$desa)!==false);
//         });
//         if(!is_null($nks)){
//           $filter_values_nks = array_filter($filter_values_desa, function($var) use ($nks){
//             return (strpos($var[7],$nks)!==false);
//           });
//           $filter_values = $filter_values_nks;
//         }else{
//           $filter_values = $filter_values_desa;
//         }
//       }else{
//         $filter_values = $filter_values_kec;
//       }
//     }else{
//       $filter_values = $filter_values_kab;
//     }
//   }else{
//     $filter_values = NULL;
//   }
//   return $filter_values;
// }
function get_filter_wilayah($tingkatan, $kab = NULL, $kec = NULL, $desa = NULL, $nks = NULL, $noruta = NULL){
  if ($tingkatan == 1){
    $filter_values = get_from_pcl();
  }
  if ($tingkatan == 2){
    $filter_values = get_from_pml();
  }
  if ($tingkatan == 3){
    $filter_values = get_from_editor();
  }
  if(!is_null($kab)){
    $filter_values = array_filter($filter_values, function($var) use ($kab){
      return (strpos($var[4],$kab)!==false);
    });
  }
  if(!is_null($kec)){
    $filter_values = array_filter($filter_values, function($var) use ($kec){
      return (strpos($var[5],$kec)!==false);
    });
  }
  if(!is_null($desa)){
    $filter_values = array_filter($filter_values, function($var) use ($desa){
      return (strpos($var[6],$desa)!==false);
    });
  }
  if(!is_null($nks)){
    $filter_values = array_filter($filter_values, function($var) use ($nks){
      return (strpos($var[7],$nks)!==false);
    });
  }
  if(!is_null($noruta)){
    $filter_values = array_filter($filter_values, function($var) use ($noruta){
      return (strpos($var[8],$noruta)!==false);
    });
  }
  return $filter_values;
}

//fungsi untuk mendapatkan jumlah progres tiap wilayah
//input $tingkatan = numeric 1,2,3 -> 1 pcl, 2 pml, 3 editor, untuk variabel lain bertipe string
//output berupa 2 dimensional array array[0][] -> nama, array[1][] -> jumlahnya
function get_progress_wilayah($tingkatan, $prov = TRUE, $kab = NULL, $kec = NULL, $desa = NULL){
  if ($tingkatan == 1){
    $values = get_from_pcl();
  }
  if ($tingkatan == 2){
    $values = get_from_pml();
  }
  if ($tingkatan == 3){
    $values = get_from_editor();
  }
  $t = name_array($values);
  if ($prov == TRUE){
    if (!is_null($kab)){
      if (!is_null($kec)){
        if (!is_null($desa)){
          $progres_desa = array();
            $filter_desa = get_filter_wilayah($tingkatan,$kab,$kec,$desa);
            if (!is_null($filter_desa)){
              $t = transposeData($filter_desa);
          //get nama desa
              $a = array_unique($t[7]);
              sort($a);
              $i = 0;
              for($i==0;$i<count($a);$i++){
                $progres_desa[$i] = get_filter_wilayah($tingkatan,$kab,$kec,$desa,$a[$i]);
                $progres_desa[$i] = count($progres_desa[$i]);
              }
              $gabung = array($a,$progres_desa);
            }else{
              $gabung = array(null,null);
            }
        }else{
          $progres_kec = array();
            $filter_kec = get_filter_wilayah($tingkatan,$kab,$kec);
            if (!is_null($filter_kec)){
              $t = transposeData($filter_kec);
          //get nama desa
              $a = array_unique($t[6]);
              sort($a);
              $i = 0;
              for($i==0;$i<count($a);$i++){
                $progres_kec[$i] = get_filter_wilayah($tingkatan,$kab,$kec,$a[$i]);
                $progres_kec[$i] = count($progres_kec[$i]);
              }
              $gabung = array($a,$progres_kec);
            }else{
              $gabung = array(null,null);
            }
        }
      }else{
        $progres_kab = array();
        $filter_kab = get_filter_wilayah($tingkatan,$kab);
        $t = transposeData($filter_kab);
        //get nama kecamatan
        $a = array_unique($t[5]);
        sort($a);
        $i = 0;
        for($i==0;$i<count($a);$i++){
          $progres_kab[$i] = get_filter_wilayah($tingkatan,$kab,$a[$i]);
          $progres_kab[$i] = count($progres_kab[$i]);
        }
        $gabung = array($a,$progres_kab);
      }
      }else{
        $progres_prov = array();
        $a = array_unique($t['Kabupaten']);
        sort($a);
        $i = 0;
        for($i==0;$i<count($a);$i++){
          $progres_prov[$i] = get_filter_wilayah($tingkatan,$a[$i]);
          $progres_prov[$i] = count($progres_prov[$i]);
        }
        $gabung = array($a,$progres_prov);
      }
    }
    return $gabung;
  }

//output -> jumlah yang sama
//filter perbandingan variabel per wilayah antara pcl dan pml
//input variabel berupa index yang sesuai dengan index kolom di excel
function get_filter_pclpml($variabel = NULL, $kab = NULL, $kec = NULL, $desa = NULL, $nks = NULL){
  $filter_values_pcl = get_filter_wilayah(1, $kab, $kec, $desa, $nks);
  $filter_values_pml = get_filter_wilayah(2, $kab, $kec, $desa, $nks);
  $t_pcl = transposeData($filter_values_pcl);
  $t_pml = transposeData($filter_values_pml);
  $unique_noruta_pcl = array_unique($t_pcl[8]);
  $unique_noruta_pml = array_unique($t_pml[8]);
  sort($unique_noruta_pcl);
  sort($unique_noruta_pml);

  $i = 0;
  $banding = array();
  for ($i=0;$i<count($unique_noruta_pcl);$i++){
    $temp_pcl = get_filter_wilayah(1, $kab, $kec, $desa, $nks, $unique_noruta_pcl[$i]);
    $temp_pcl = transposeData($temp_pcl);
    $temp_pcl = implode($temp_pcl[$variabel]);
    $temp_pml = get_filter_wilayah(2, $kab, $kec, $desa, $nks, $unique_noruta_pml[$i]);
    $temp_pml = transposeData($temp_pml);
    $temp_pml = implode($temp_pml[$variabel]);
    if($temp_pcl == $temp_pml){
      $banding[$i] = 1;
    }else{
      $banding[$i] = 0;
    }
  }
  $jml_banding = array_sum($banding);
  return $jml_banding;
}

//progres perbandingan isian pcl dan pml berdasarkan tingkatan dan $variabel
//input variabel berupa index yang sesuai dengan index kolom di excel
//output berupa array -> belum fix
function get_progress_pclpml($variabel, $kab, $kec = NULL, $desa = NULL){
  $filter_values_pcl = get_filter_wilayah(1, $kab = $kab, $kec = $kec, $desa = $desa);
  $t_pcl = transposeData($filter_values_pcl);
  $unique_nks_pcl = array_unique($t_pcl[7]);
  $i = 0;
  $progres = array();
  for($i=0;$i<count($unique_nks_pcl);$i++){
    $temp_progres = get_filter_pclpml($variabel, $kab, $kec, $desa, $unique_nks_pcl[$i]);
    $progres[$i] = $temp_progres;
  }
  return $progres;
}
