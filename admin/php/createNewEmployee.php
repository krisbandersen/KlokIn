<?php
require_once '../../php/sendValidationEmail.php';

$errors = [];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] !== true) {
  header("Location: /login.php");
  exit;
}

// Validate POST data
if (!isset($_POST['firstname']) || strlen($_POST['firstname']) > 255 || !preg_match('/^[a-zA-ZÆØÅæøå -]+$/', $_POST['firstname'])) {
    $errors[] = 1; // Invalid firstname
}
if (!isset($_POST['lastname']) || strlen($_POST['lastname']) > 255 || !preg_match('/^[a-zA-ZÆØÅæøå -]+$/', $_POST['lastname'])) {
    $errors[] = 1; // Invalid lastname
}
if (!isset($_POST['email']) || strlen($_POST['email']) > 255 || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 2; // Invalid email
}
if (!isset($_POST['employee_number']) || strlen($_POST['employee_number']) > 255) {
    $errors[] = 1; // Invalid password
}

// Ensure no errors so far
if (count($errors) === 0) {
    if (isset($_POST['csrf_token']) && validateToken($_POST['csrf_token'])) {
        // Connect to the database
        $C = connect();
        if ($C) {
            // Retrieve the organization ID from the logged-in user
            if (isset($_SESSION['organization_id'])) {
                $org_id = $_SESSION['organization_id']; // Get the org ID from session

                // Check if an organization exists with the provided ID
                $org_res = sqlSelect($C, 'SELECT id, label_name FROM organizations WHERE id=?', 'i', $org_id);
                
                $results = $org_res->fetch_assoc();

                if ($org_res && $org_res->num_rows === 1) {     
                    // Check if the employee_number already exists within the organization
                    $employee_res = sqlSelect($C, 'SELECT id FROM employees WHERE employee_number = ? AND organization_id = ?', 
                                              'si', $_POST['employee_number'], $org_id);
                    
                    if ($employee_res->num_rows > 0) {
                        // Employee number already exists for the organization
                        $errors[] = 8; // Employee number already exists error               
                    } else {
                      $emp_id = sqlInsert($C, 'INSERT INTO employees (firstname, lastname, email, employee_number, organization_id) VALUES (?, ?, ?, ?, ?)', 
                                            'ssssi', $_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['employee_number'], $org_id);
                        
                        if ($emp_id !== -1) {
                            if (sendEmail($_POST['email'], '' . $_POST['firstname'] . '' . $_POST['lastname'] .  '', 'Login til KlokIn tidsregistrering', '<!doctype html>
                            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
                              <head>
                                <title></title>
                                <!--[if !mso]><!-->
                                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                                <!--<![endif]-->
                                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1">
                                <style type="text/css">
                                  #outlook a { padding:0; }
                                  body { margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%; }
                                  table, td { border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt; }
                                  img { border:0;height:auto;line-height:100%; outline:none;text-decoration:none;-ms-interpolation-mode:bicubic; }
                                  p { display:block;margin:13px 0; }
                                </style>
                                <!--[if mso]>
                                <noscript>
                                <xml>
                                <o:OfficeDocumentSettings>
                                  <o:AllowPNG/>
                                  <o:PixelsPerInch>96</o:PixelsPerInch>
                                </o:OfficeDocumentSettings>
                                </xml>
                                </noscript>
                                <![endif]-->
                                <!--[if lte mso 11]>
                                <style type="text/css">
                                  .mj-outlook-group-fix { width:100% !important; }
                                </style>
                                <![endif]-->
                                
                                  <!--[if !mso]><!-->
                                    <link href="https://fonts.googleapis.com/css?family=Ubuntu:400,700" rel="stylesheet" type="text/css">
                                    <style type="text/css">
                                      @import url(https://fonts.googleapis.com/css?family=Ubuntu:400,700);
                                    </style>
                                  <!--<![endif]-->

                                
                                
                                <style type="text/css">
                                  @media only screen and (min-width:480px) {
                                    .mj-column-per-33-333333333333336 { width:33.333333333333336% !important; max-width: 33.333333333333336%; }
                            .mj-column-per-100 { width:100% !important; max-width: 100%; }
                                  }
                                </style>
                                <style media="screen and (min-width:480px)">
                                  .moz-text-html .mj-column-per-33-333333333333336 { width:33.333333333333336% !important; max-width: 33.333333333333336%; }
                            .moz-text-html .mj-column-per-100 { width:100% !important; max-width: 100%; }
                                </style>
                                
                              
                                <style type="text/css">
                                
                                

                                @media only screen and (max-width:479px) {
                                  table.mj-full-width-mobile { width: 100% !important; }
                                  td.mj-full-width-mobile { width: auto !important; }
                                }
                              
                                </style>
                                <style type="text/css">
                                .hide_on_mobile { display: none !important;} 
                                    @media only screen and (min-width: 480px) { .hide_on_mobile { display: block !important;} }
                                    .hide_section_on_mobile { display: none !important;} 
                                    @media only screen and (min-width: 480px) { 
                                        .hide_section_on_mobile { 
                                            display: table !important;
                                        } 

                                        div.hide_section_on_mobile { 
                                            display: block !important;
                                        }
                                    }
                                    .hide_on_desktop { display: block !important;} 
                                    @media only screen and (min-width: 480px) { .hide_on_desktop { display: none !important;} }
                                    .hide_section_on_desktop { 
                                        display: table !important;
                                        width: 100%;
                                    } 
                                    @media only screen and (min-width: 480px) { .hide_section_on_desktop { display: none !important;} }
                                    
                                      p, h1, h2, h3 {
                                          margin: 0px;
                                      }

                                      ul, li, ol {
                                        font-size: 11px;
                                        font-family: Ubuntu, Helvetica, Arial;
                                      }

                                      a {
                                          text-decoration: none;
                                          color: inherit;
                                      }

                                    @media only screen and (max-width:480px) {
                                        .mj-column-per-33 { width:100%!important; max-width:100%!important; }.mj-column-per-100 > .mj-column-per-33 { width:33.333333333333336%!important; max-width:33.333333333333336%!important; }.mj-column-per-100 { width:100%!important; max-width:100%!important; }.mj-column-per-100 > .mj-column-per-100 { width:100%!important; max-width:100%!important; }
                                    }
                                </style>
                                
                              </head>
                              <body style="word-spacing:normal;background-color:#FFFFFF;">
                                
                                
                                  <div style="background-color:#FFFFFF;">
                                    
                                  
                                  <!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" bgcolor="#000000" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                                
                                  
                                  <div style="background:#000000;background-color:#000000;margin:0px auto;max-width:600px;">
                                    
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#000000;background-color:#000000;width:100%;">
                                      <tbody>
                                        <tr>
                                          <td style="direction:ltr;font-size:0px;padding:18px 26px 18px 26px;text-align:center;">
                                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:182.66666666666669px;" ><![endif]-->
                                        
                                  <div class="mj-column-per-33-333333333333336 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                    
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                    <tbody>
                                      
                                    </tbody>
                                  </table>
                                
                                  </div>
                                
                                      <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:182.66666666666669px;" ><![endif]-->
                                        
                                  <div class="mj-column-per-33-333333333333336 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                    
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                    <tbody>
                                      
                                          <tr>
                                            <td align="center" style="font-size:0px;padding:0px 0px 0px 0px;word-break:break-word;">
                                              
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
                                    <tbody>
                                      <tr>
                                        <td style="width:137px;">
                                          
                                  <img src="https://cdn.discordapp.com/attachments/1102559437115367454/1290371779830878348/manifest-icon-512.maskable.png?ex=66fc37de&is=66fae65e&hm=e0c92d0e8bc4286506b4f9f3e6e1e7a559a55b1317654a1b5663cc1c9757fdb9&" style="border:0;border-radius:0px 0px 0px 0px;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="137" height="auto">
                                
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                                
                                            </td>
                                          </tr>
                                        
                                    </tbody>
                                  </table>
                                
                                  </div>
                                
                                      <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:182.66666666666669px;" ><![endif]-->
                                        
                                  <div class="mj-column-per-33-333333333333336 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                    
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                    <tbody>
                                      
                                          <tr>
                                            <td style="font-size:0px;word-break:break-word;">
                                              
                                  <div style="height:30.390625px;line-height:30.390625px;">&#8202;</div>
                                
                                            </td>
                                          </tr>
                                        
                                    </tbody>
                                  </table>
                                
                                  </div>
                                
                                      <!--[if mso | IE]></td></tr></table><![endif]-->
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                    
                                  </div>
                                
                                  
                                  <!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" role="presentation" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
                                
                                  
                                  <div style="margin:0px auto;max-width:600px;">
                                    
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
                                      <tbody>
                                        <tr>
                                          <td style="direction:ltr;font-size:0px;padding:10px 0px 10px 0px;text-align:center;">
                                            <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
                                        
                                  <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
                                    
                                  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
                                    <tbody>
                                      
                                          <tr>
                                            <td align="left" style="font-size:0px;padding:15px 15px 15px 15px;word-break:break-word;">
                                              
                                  <div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1.5;text-align:left;color:#000000;"><p style="font-family: Ubuntu, sans-serif; font-size: 11px;">Du er nu oprettet som ny bruger hos ' . htmlentities($results["label_name"], ENT_QUOTES, 'UTF-8') . '</p>
                            <p style="font-family: Ubuntu, sans-serif; font-size: 11px;">&nbsp;</p>
                            <p style="font-family: Ubuntu, sans-serif; font-size: 11px;">Dit login til at tilg&aring; KlokIn tidsregistrering:</p>
                            <p style="font-family: Ubuntu, sans-serif; font-size: 11px;">Email: ' . $_POST['email'] . '</p>
                            <p style="font-family: Ubuntu, sans-serif; font-size: 11px;">Kodeord: ' . $_POST['employee_number'] . ' </p>
                            <p style="font-family: Ubuntu, sans-serif; font-size: 11px;">&nbsp;</p>
                            <p style="font-family: Ubuntu, sans-serif; font-size: 11px;">L&aelig;s mere om hvordan du bruger KlokIn tidsregistrering <a href="https://localhost/app" target="_blank" rel="noopener" style="color: #1663a1;"><span style="text-decoration: underline;">her</span></a>.</p></div>
                                
                                            </td>
                                          </tr>
                                        
                                    </tbody>
                                  </table>
                                
                                  </div>
                                
                                      <!--[if mso | IE]></td></tr></table><![endif]-->
                                          </td>
                                        </tr>
                                      </tbody>
                                    </table>
                                    
                                  </div>
                                
                                  
                                  <!--[if mso | IE]></td></tr></table><![endif]-->
                                
                                
                                  </div>
                                
                              </body>
                            </html>
                              ')) {
                                $errors[] = 0; // Success
                            } else {
                                $errors[] = 9; // Email sending error
                            }
                          } else {
                              // Failed to insert employee into the database
                              $errors[] = 6; // Database insert error
                          }
                        }
                } else {
                    $errors[] = 7; // Organization not found
                }
            }
        } else {
            // Database connection failed
            $errors[] = 6; // Database connection error
        }
    } else {
        // Invalid CSRF Token
        $errors[] = 9; // Invalid token
    }
}

// Output the result as JSON
echo json_encode($errors);
