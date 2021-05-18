<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Slim\Factory\AppFactory;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';


$app = AppFactory::create();
$app->addRoutingMiddleware();
$twig = Twig::create('templates', ['cache' => false]);  // => 'cache' 
$app->add(TwigMiddleware::create($app, $twig));

$errorMiddleware = $app->addErrorMiddleware(true, true, true);  // !improtant


//global value   
$css_bootstrap = [
    'reboot' => ' <link rel="stylesheet" href="css/bootstrap-reboot.min.css">',
    'grid' => ' <link rel="stylesheet" href="css/bootstrap-grid.min.css">',
    'icons' => '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css">'  
]; 
$my_css = [
    'hamburger' => '<link rel="stylesheet" href="css/hamburgers.min.css">',
    'footer_fixed' => '<link rel="stylesheet" href="css/footer-fixed.css">',
    'general_css' => '<link rel="stylesheet" href="css/style.css">',
    'fonts' => ' <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;700&display=swap" rel="stylesheet">'
];
$script = [
    'jquery' => '<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>',
    'password' => '<script src="js/password.js"></script>',
    'hamburger' => '<script src="js/hamburger.js"></script>',
    'footer_display' => '<script src="js/footer_display.js"></script>',
    'left_navbar' => '<script src="js/left_navbar.js"></script>'
];



$app->get('/', function ($request, $response, $args) use($css_bootstrap,$my_css,$script) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'home.twig',[
        'title' => 'home',
        'style' =>$css_bootstrap['reboot'].$css_bootstrap['grid'].$css_bootstrap['icons'].$my_css['fonts'].$my_css['hamburger'].$my_css['general_css'],
        'script' => $script['jquery'].$script['hamburger']
        ]
    );
});
$app->get('/login', function ($request, $response, $args) use($css_bootstrap,$my_css,$script) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'login.twig',[
        'title' => 'login',
        'style' =>$css_bootstrap['reboot'].$css_bootstrap['grid'].$css_bootstrap['icons'].$my_css['fonts'].$my_css['hamburger'].$my_css['general_css'].$my_css['footer_fixed'],
        'script' => $script['jquery'].$script['hamburger'].$script['footer_display']
        ]
    );
});
$app->post('/login', function(Request $request, Response $response, $args)  {
    $view = Twig::fromRequest($request);
    $params = $request->getParsedBody();
    // $email_error = '';
    // $password_error = ''; 
    $email = htmlspecialchars($params["email"]); 
    $password = md5(md5(trim($params['password'])));
    $result = Log_In($email,$password); 

    return $view->render($response, 'result_login.twig',[
         'title' => 'result',
         'result_login' => $result
        ]
    ); 
}); 
function Log_In($email, $password) {
    $db =  new SqLite3('data/data.db');
    $sql = "SELECT password,status FROM user_login WHERE email="."'".$email."'"; 
    $result = $db->query($sql); 
    while ($row = $result->fetchArray()) {
        if($row[0] == $password && $row[1] == 1) {
            $db->close();
            return 1; 
        }
    }
    $db->close();
    return 0; 
    
}

$app->get('/register', function ($request, $response, $args) use($css_bootstrap,$my_css,$script) {
    $view = Twig::fromRequest($request);
    $error_email ='This email already exist'; 
    return $view->render($response, 'register.twig',[
        'title' => 'sing up',
        'style' =>$css_bootstrap['reboot'].$css_bootstrap['grid'].$css_bootstrap['icons'].$my_css['fonts'].$my_css['hamburger'].$my_css['general_css'].$my_css['footer_fixed'],
        'script' => $script['jquery'].$script['hamburger'].$script['footer_display'].$script['password']
        ]
    );
});

$app->post('/register',function(Request $request, Response $response, $args) use($css_bootstrap,$my_css,$script) {
    $view = Twig::fromRequest($request);
    $params = $request->getParsedBody();
    
    $email_error = '';
    $username_error = ''; 
    
    $email = htmlspecialchars($params["email"]); 
    $username = htmlspecialchars($params["username"]); 
    $password = md5(md5(trim($params['password'])));
    $hash = md5($username . time()); // for activation 

    $bool_email = checkEmail($email); 
    $bool_username = checkUsername($username); 

    if (!$bool_email) { $email_error = 'This email adress already exist!'; }
    if (!$bool_username) { $username_error = 'This username already exist!'; }
    if (!$bool_username || !$bool_email ) {
        return $view->render($response, 'register.twig',[
                    'title' => 'error',
                    'style' =>$css_bootstrap['reboot'].$css_bootstrap['grid'].$css_bootstrap['icons'].$my_css['hamburger'].$my_css['general_css'].$my_css['footer_fixed'],
                    'script' => $script['jquery'].$script['hamburger'].$script['footer_display'].$script['password'],
                    'email_error' =>  $email_error,
                    'username_error' => $username_error
                    ]
                );
    }else {
        createdUser($email,$username,$password,$hash); 
        sendEmailVar($email,$username,$hash);
        $msg = 'Your account has been made, <br /> please verify it by clicking the activation link that has been send to your email.';
        return $view->render($response, 'user_created.twig',[
            'msg' => $msg,
            'title' => 'created',
            'style' =>$css_bootstrap['reboot'].$css_bootstrap['grid'].$css_bootstrap['icons'].$my_css['fonts'].$my_css['hamburger'].$my_css['general_css'].$my_css['footer_fixed'],
            'script' => $script['jquery'].$script['hamburger'].$script['footer_display']
            ]
        );        
    }
}); 
// return false if email already exist in data.db 
function checkEmail($email)  {
    $db =  new SqLite3('data/data.db');
    $sql = "SELECT email FROM user_login WHERE email="."'".$email."'"; 
    $results = $db->query($sql);
    while ($row = $results->fetchArray()) {
        if  ($row[0] == $email) {
           $db->close();
           return false; 
        }
    }
    $db->close();
    return true; 
}
// return false if username already exist in data.db 
function checkUsername($username) {
    $db =  new SqLite3('data/data.db');
    $sql = "SELECT username FROM user_login WHERE username="."'".$username."'"; 
    $results = $db->query($sql);
    while ($row = $results->fetchArray()) {
        if  ($row[0] == $username) {
           $db->close();
           return false; 
        }
    }
    $db->close();
    return true; 
}
function createdUser($email, $username, $password, $hash) {
    $db =  new SqLite3('data/data.db');
    $sql  = 'INSERT INTO user_login ("email", "username", "password", "user_hash") VALUES '; 
    $sql = $sql.'("'.$email.'","'.$username.'","'.$password.'","'.$hash.'");';
    $db->exec($sql);
    $db->close();
}

function sendEmailVar($email,$username,$hash) {
  
    $mail = new PHPMailer(true);

    //Server settings
   // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'secret.baner@gmail.com';                     //SMTP username
    $mail->Password   = 'Sergeyb2e8r11';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
    $mail->Port       = 587;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
    //Recipients
    $mail->setFrom('secret.baner@gmail.com', 'web page');
    $mail->addAddress($email, $username);     //Add a recipient
    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Hello '.$username;
    $mail->Body    = 'this email was generated automatically. <a href="http://localhost:8100/confirmed/'.$username.'/'.$hash.'">Link</a>';
    $mail->send();
}
$app->get('/confirmed/{username}/{hash}', function ($request, $response, $args){
    $view = Twig::fromRequest($request);
    $hash_usr =  $args['hash']; 
    $username = $args['username']; 
    $result = Confirmed($hash_usr, $username); 
    // activation_result.twig
    return $view->render($response, 'activation_result.twig',[
            'title' => 'result_registration',
             'result' => $result
        ]
    );  
    echo $result; 
});

// return number of  1 2 3; where  1 - account already active; 2 - account activeted , 3 - something going wrong 
function Confirmed($hash_usr, $username) {
    $db =  new SqLite3('data/data.db');
    
    $num = 1; 
    $sql_hash = "SELECT user_hash FROM user_login WHERE username="."'".$username."'"; //  second  sql  
    $sql_status_check = "SELECT status FROM user_login WHERE username="."'".$username."'"; // first check status 
    $sql_status_activeting  = 'UPDATE user_login set  status = '.$num; 
    
    $results = $db->query($sql_status_check);
    while ($row = $results->fetchArray()) {
        if  ($row[0] == 1) {
            $db->close();
            return 1; 
        }
    }
    $results = $db->query($sql_hash);
    while ($row = $results->fetchArray()) {
        if  ($row[0] == $hash_usr) {
           $db->exec($sql_status_activeting);
           $db->close();
           return 2; 
        }
    }
    $db->close();
    return 3; 
} 


$app->get('/gallery/{teg}', function ($request, $response, $args){
    $view = Twig::fromRequest($request);
    return $view->render($response, 'gallery.twig',[
        'teg' => $args['teg'],
        'title' => 'Gallery',
        'describe_gallery_all' => 'Hello world :) </br>     Lorem ipsum dolor sit amet consectetur adipisicing elit. Quod amet iure sapiente error? Nulla, ea qui delectus quam quibusdam blanditiis incidunt quia, harum culpa aperiam possimus nisi quasi veniam aut.',
        'title_gallery_all' => 'title'
        ]
    );
});
$app->post('gallary/{teg}', function($request, $response, $args){
    $view = Twig::fromRequest($request);
    return $view->render($response, 'gallery.twig',[
        'teg' => $args['teg'],
        'title' => 'Gallery',
        'describe_gallery_all' => 'Hello world :) </br>     Lorem ipsum dolor sit amet consectetur adipisicing elit. Quod amet iure sapiente error? Nulla, ea qui delectus quam quibusdam blanditiis incidunt quia, harum culpa aperiam possimus nisi quasi veniam aut.',
        'title_gallery_all' => 'title'
        ]
    );
}); 
$app->get('/dbview', function($request, $response, $args){
   
    $db =  new SqLite3('data/data.db');
    $query = "SELECT * FROM user_login";
    $result = $db->query($query);
    echo "<table>";
    echo "<tr>
        <th>id_user</th>
        <th>email</th>
        <th>username</th>
        <th>password</th>
        <th>user_hash</th>
        <th>status</th>
    </tr>";
    while($row = $result->fetchArray()){   //Creates a loop to loop through results
        echo "<tr><td>".$row['id_user']."</td><td>".$row['email']."</td><td>".$row['username']."</td><td>".$row["password"]."</td><td>".$row["user_hash"]."</td><td>".$row["status"]."</td></tr>";
    }
    echo "</table>";
    $db -> close(); 
    return $response; 
}); 



$app->get('/gallery/{teg}/{id}', function ($request, $response, $args) {
    phpinfo();
});

$app->run();