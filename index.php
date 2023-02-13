<?php 
/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */


$enviroment = 'TEST';/* Change to LIVE for live mode! */

require_once 'vendor/autoload.php';
include 'includes/functions.php';

$stripe = new \Stripe\StripeClient(STRIPE_KEY);
$con = 0;
$amount = (number_format(9.99, 2, '.', '') * 100);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Sepa stripe</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">

        <link rel="stylesheet" href="style.css" />
    </head>
    <body>
        <center><h1>SEPA Direct Debit payments</h1></center>
        <div class="row">
                    
        <div class="container form-section">
        <?php
        if (isset($_POST['submitfile']))
        {
            $filename = $_FILES["file"]["name"];
            $file_basename = substr($filename, 0, strripos($filename, '.')); // get file extention
            $file_ext = substr($filename, strripos($filename, '.')); // get file name
            $filesize = $_FILES["file"]["size"];
            $allowed_file_types = array('.xls','.xlsx');
            $noError = false;	
        
            if (in_array($file_ext,$allowed_file_types))
            {
                $newfilename =$file_basename.'_'.strtolower(random_strings(4)).'_'.time(). $file_ext;
        
                move_uploaded_file($_FILES["file"]["tmp_name"], "uploads/" . $newfilename);
                

                $file_name = 'uploads/'.$newfilename;
                $file_type = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file_name);
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($file_type);
                $spreadsheet = $reader->load($file_name);
                $sheet_rows = $spreadsheet->getActiveSheet()->toArray();
                  
                  

                    foreach($sheet_rows as $sheet_row)
                    {
                        if($con> 0){

                            if(!empty($sheet_row[0])){
                        
                            try {

                                $creating_source = $stripe->sources->create(
                                    [
                                        'type' => 'sepa_debit',
                                        'sepa_debit' => ['iban' => $sheet_row[0]],
                                        'currency' => 'eur',
                                        'amount' => $amount,
                                        'owner' => [
                                        'name'=> $sheet_row[1],
                                        
                                        ],
                                    ]
                                );

                                $ibanData = ibanCurlValidator($sheet_row[0], true);
                            
                                $cusotmer = $stripe->customers->create(
                                    ['email' => strtolower(sanitizeString($sheet_row[1]).'_'.random_strings(4)).'@annuaire-europeen.com',
                                    'name'=> $sheet_row[1],
                                    'address' => !$ibanData ? null : $ibanData,
                                    'source' => $creating_source->id
                                    ]
                                );

                                $add_subscriptions = $stripe->subscriptions->create([
                                    'customer' => $cusotmer->id,
                                    'items' => [
                                    ['price' => PRICE_KEY],
                                    ],
                                ]);
                                $noError = true;                              
                                
                            }
                            
                            //catch exception
                            catch(Exception $e) {

                                echo '<div class="alert alert-danger">'.$sheet_row[0] .$e->getMessage().'</div>';
                                //continue;
                            }

                            }     

                        }
                        $con++;
                    } 
                    if($noError){
                        echo '<div class="alert alert-success">Sucessfully Created the records!</div>';
                    }       
        
            }
            else{
                echo '<div class="alert alert-danger">Only these file typs are allowed for upload: <strong>'.implode(', ',$allowed_file_types).'</strong></div>';
        
            }
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
            
                <div class="form-group">
                    <label for="file-input"></label>
                    <input type="file"  id="file-input" name="file" accept=".xls,.xlsx" />
                </div>
                <div class="form-group">

                    <input type="submit" value="Upload" style="width:100%;" name="submitfile">
                </div>
          
        </form>
        </div>
        </div>
    </body>
</html>
