<?php
/**
 * 	Descrição: Webhook para comunicação com a plataforma HUGGY
 *  Autor: Thiago Gham 
 *	Data: 05-01-2018 
 */
require_once 'Huggy.class.php';

header('Content-Type: application/json');

$token 		= Huggy::$token_webhook;
$parametros = json_decode(@file_get_contents('php://input'));

if(isset($parametros->token)){
	if($parametros->token === $token){	

		$function = Huggy::SalvaLog( $parametros );
		
		if(!empty($function)){

			$api = new Huggy();

			if(method_exists($api, $function)){
		
				$DADOS = $api->$function( $parametros );

				switch ($function) {
					case 'answeredChatForm':
						//code here
						exit(header("HTTP/1.1 200"));
						break;
					default:
						exit(header("HTTP/1.1 404"));
						break;
				}
			}
		}
	}
}

header("HTTP/1.1 404");