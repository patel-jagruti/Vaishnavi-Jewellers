<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = trim((string)($input['email'] ?? ''));

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    $pdo = db();
    
    // Insert subscriber
    $stmt = $pdo->prepare("INSERT IGNORE INTO subscribers (email) VALUES (:email)");
    $stmt->execute([':email' => $email]);
    
    $subject = "Welcome to Vaishnavi Jewellers - Vault Member Added";
    
    $bodyHtml = "
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset='UTF-8'>
      <title>Welcome to Vaishnavi Jewellers</title>
      <link href='https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;600&display=swap' rel='stylesheet'>
    </head>
    <body style='margin: 0; padding: 0; background-color: #0F0104; font-family: \"Poppins\", Arial, sans-serif;'>
      <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background-color: #0F0104; padding: 40px 20px;'>
        <tr>
          <td align='center'>
            <table width='600' border='0' cellspacing='0' cellpadding='0' style='background-color: #1A0308; border-radius: 24px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.6); border: 1.5px solid #D4AF37;'>
              
              <!-- HEADER -->
              <tr>
                <td align='center' style='background: linear-gradient(135deg, #4A0E1A 0%, #200208 100%); padding: 45px 20px; border-bottom: 2px solid #D4AF37;'>
                  <div style='font-family: \"Playfair Display\", Georgia, serif; font-size: 32px; font-weight: 700; color: #FFFFFF; letter-spacing: 2px;'>VAISHNAVI</div>
                  <div style='font-size: 11px; color: #D4AF37; letter-spacing: 5px; font-weight: 600; margin-top: 5px; text-transform: uppercase;'>Jewellers</div>
                </td>
              </tr>

              <!-- BODY -->
              <tr>
                <td style='padding: 45px 35px;'>
                  <div style='text-align: center; margin-bottom: 30px;'>
                    <div style='font-size: 36px; color: #D4AF37; margin-bottom: 10px;'>✦</div>
                    <h1 style='font-family: \"Playfair Display\", Georgia, serif; font-size: 26px; color: #FFFFFF; margin: 10px 0 5px 0; font-weight: 700; letter-spacing: 0.5px;'>Welcome to the Vault</h1>
                    <p style='font-size: 13.5px; color: #E8E0D6; line-height: 1.6; max-width: 480px; margin: 10px auto 0 auto;'>
                      Thank you for joining our inner circle. Below, you will find your exclusive luxury membership credential.
                    </p>
                  </div>

                  <!-- VIP MEMBER CARD -->
                  <div style='margin: 35px auto; max-width: 420px;'>
                    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='background: linear-gradient(135deg, #2D050D 0%, #150205 100%); border-radius: 20px; border: 2px solid #D4AF37; box-shadow: 0 15px 40px rgba(122,30,58,0.4); overflow: hidden; position: relative;'>
                      <tr>
                        <td style='padding: 30px 25px;'>
                          <!-- Top Row -->
                          <table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin-bottom: 35px;'>
                            <tr>
                              <td>
                                <div style='font-family: \"Playfair Display\", Georgia, serif; font-size: 18px; font-weight: 700; color: #FFFFFF; letter-spacing: 1px;'>VAISHNAVI</div>
                                <div style='font-size: 9px; color: #D4AF37; letter-spacing: 3px; font-weight: 600; text-transform: uppercase;'>Jewellers</div>
                              </td>
                              <td align='right' style='vertical-align: top;'>
                                <div style='font-size: 22px; color: #D4AF37;'>✦</div>
                              </td>
                            </tr>
                          </table>
                          
                          <!-- Card Label -->
                          <div style='font-size: 9px; color: #D4AF37; letter-spacing: 3px; text-transform: uppercase; font-weight: 600; margin-bottom: 5px;'>VIP Privilege Pass</div>
                          <div style='font-family: \"Playfair Display\", Georgia, serif; font-size: 22px; color: #FFFFFF; margin-bottom: 25px; font-weight: 600; letter-spacing: 0.5px;'>VAULT MEMBER</div>
                          
                          <!-- Footer Row -->
                          <table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin-top: 15px;'>
                            <tr>
                              <td>
                                <div style='font-size: 9px; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 1px;'>Member ID</div>
                                <div style='font-family: monospace; font-size: 14px; color: #D4AF37; font-weight: bold; letter-spacing: 2px;'>VJ-" . rand(1000, 9999) . "-" . rand(10, 99) . "</div>
                              </td>
                              <td align='right' style='vertical-align: bottom;'>
                                <div style='font-size: 9px; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 1px;'>Status</div>
                                <div style='font-size: 12px; color: #FFFFFF; font-weight: 600; letter-spacing: 0.5px;'>ACTIVE</div>
                              </td>
                            </tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </div>

                  <div style='border-top: 1px solid rgba(212,175,55,0.15); border-bottom: 1px solid rgba(212,175,55,0.15); padding: 25px 10px; margin-bottom: 35px;'>
                    <h4 style='font-family: \"Playfair Display\", Georgia, serif; font-size: 17px; color: #D4AF37; margin-top: 0; margin-bottom: 15px; text-align: center; letter-spacing: 0.5px;'>Exclusive Circle Privileges:</h4>
                    <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                      <tr>
                        <td style='padding: 5px 0; font-size: 13.5px; color: #E8E0D6;'><span style='color: #D4AF37; margin-right: 8px;'>✦</span> <strong>VIP Previews</strong>: Immediate access to seasonal designer launches.</td>
                      </tr>
                      <tr>
                        <td style='padding: 5px 0; font-size: 13.5px; color: #E8E0D6;'><span style='color: #D4AF37; margin-right: 8px;'>✦</span> <strong>Private Showrooms</strong>: Invite-only trunk shows and exhibitions.</td>
                      </tr>
                      <tr>
                        <td style='padding: 5px 0; font-size: 13.5px; color: #E8E0D6;'><span style='color: #D4AF37; margin-right: 8px;'>✦</span> <strong>Bespoke Curation</strong>: Direct video chat consultations with our master jewelers.</td>
                      </tr>
                    </table>
                  </div>

                  <div style='text-align: center;'>
                    <a href='http://localhost:8000/visnavi%20jwallers/pages/shop.php' style='background: linear-gradient(135deg, #D4AF37 0%, #AA7C11 100%); color: #0F0104; text-decoration: none; padding: 16px 40px; border-radius: 50px; font-weight: 600; font-size: 13.5px; display: inline-block; box-shadow: 0 8px 20px rgba(170,124,17,0.35);'>Access Showroom</a>
                  </div>
                </td>
              </tr>

              <!-- FOOTER -->
              <tr>
                <td style='background-color: #0F0104; border-top: 1px solid rgba(212,175,55,0.15); padding: 35px 20px; text-align: center;'>
                  <div style='font-size: 11px; color: #888888;'>© 2026 Vaishnavi Jewellers. Heritage & Luxury.</div>
                  <div style='font-size: 10px; color: #666666; margin-top: 5px;'>You received this email because you subscribed to our newsletter.</div>
                </td>
              </tr>

            </table>
          </td>
        </tr>
      </table>
    </body>
    </html>";

    $bodyText = "Welcome to Vaishnavi Jewellers!\n\nThank you for subscribing to our newsletter. Explore our latest premium catalog collections at: http://localhost:8000/visnavi%20jwallers/pages/shop.php";
    
    send_email($email, "Subscriber", $subject, $bodyHtml, $bodyText);

    echo json_encode(['success' => true, 'message' => 'Thank you for subscribing! We have sent a welcome confirmation email.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Subscription failed: ' . $e->getMessage()]);
}
