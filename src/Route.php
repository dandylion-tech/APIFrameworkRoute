<?php
namespace Dandylion;
http_response_code(404);
$_POST = array_merge($_POST,json_decode(file_get_contents('php://input'),true)??[]);
class Route {
    public static function route($path, $file_location){
        header("Content-Type: application/json");
        extract($GLOBALS);
        $url_check = preg_replace("/\/{[a-zA-Z0-9\-_]+}/","/([a-zA-Z0-9\-_]+)",$path);
        $path_info = pathinfo($_SERVER['REQUEST_URI']);
        $request_path = ($path_info["dirname"]=="/"||$path_info["dirname"]=="\\"?"/":$path_info["dirname"]."/").explode("?",$path_info["basename"])[0];
        preg_match_all("/^".str_replace("/","\/",$url_check)."$/",$request_path,$value_list,PREG_SET_ORDER);
        if(preg_match_all("/^".str_replace("/","\/",$url_check)."$/",$request_path,$value_list,PREG_SET_ORDER)&&count($value_list)>0){
            $value_list = $value_list[0];
            array_shift($value_list);
            preg_match_all("/\/{([a-zA-Z0-9\-_]+)}/",$path,$key_list);
            $url_variables = array_combine($key_list[1],$value_list);
            $_GET = array_merge($_GET,$url_variables);
            include $_SERVER["DOCUMENT_ROOT"]."/../api/".$file_location;
            $methods = ["get","post","put","delete","patch"];
            $method_list = [];
            foreach($methods as $method){
                if(function_exists($method))$method_list[] = strtoupper($method);
            }
            $method_list[] = "OPTIONS";
            header("Allow: ".implode(", ",$method_list));
            header("Access-Control-Allow-Methods: ".implode(", ",$method_list));
            switch($_SERVER["REQUEST_METHOD"]){
                case "OPTIONS":
                    http_response_code(204);
                    exit;
                default:
                    $method = $_SERVER["REQUEST_METHOD"];
                    if(function_exists($method)){
                        http_response_code(200);
                        echo json_encode($method());
                    } else {
                        http_response_code(405);
                    }
                    exit;
            }
        }
    }
}
