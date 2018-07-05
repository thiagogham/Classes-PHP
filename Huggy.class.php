<?php
/**
 *  Descrição: Classe para consumir a API da Huggy
 *	Autor: Thiago Gham
 *	Data: 02/01/2018
 */
class Huggy{
	/**
	 * 
	 * [$api description]
	 * @var string
	 */
	private $api = 'https://api.huggy.io/v2/';
	/**
	 * [$token description]
	 * @var string
	 */
	private $token = '';
	/**
	 * [$token_webhook description]
	 * @var string
	 */
	public static $token_webhook = '';
	/**
	 * [$cd_empresa description]
	 * @var string
	 */
	private $cd_empresa = '';
	/**
	 * [$header description]
	 * @var [type]
	 */
	private $headers = array();
	/**
	 * [$body description]
	 * @var [type]
	 */
	private $body = '';
	/**
	 * [$http_status description]
	 * @var string
	 */
	private $http_code = '';
	/**
	 * [$http_status_msn description]
	 * @var string
	 */
	private $http_status_msn = '';
	/**
	 * [$curl description]
	 * @var string
	 */
	private $curl = '';
	/**
	 * [$timeout description]
	 * @var integer
	 */
	private $timeout = 30;
	/**
	 * [$driver_log description]
	 * @var string
	 */
	private static  $driver_log = '';
	/**
	 * [$server_log description]
	 * @var string
	 */
	private static $server_log = 'localhost';
	/**
	 * [$database_log description]
	 * @var string
	 */
	private static $database_log = '';
	/**
	 * [$username_log description]
	 * @var string
	 */
	private static $username_log = '';
	/**
	 * [$password_log description]
	 * @var string
	 */
	private static $password_log = '';

	/**
	 * [$driver_log description]
	 * @var string
	 */
	private static  $driver_db = '';
	/**
	 * [$server_log description]
	 * @var string
	 */
	private static $server_db = 'localhost';
	/**
	 * [$database_log description]
	 * @var string
	 */
	private static $database_db = '';
	/**
	 * [$username_log description]
	 * @var string
	 */
	private static $username_db = '';
	/**
	 * [$password_log description]
	 * @var string
	 */
	private static $password_db = '';

	/**
	 * [__construct description]
	 */
	public function __construct(){

	}
	/**
	 * [createdChat description]
	 * @param  string $paramentros [description]
	 * @return [type]              [description]
	 */
	public function createdChat($json = ''){

		if(!empty($json)){
			if(!is_object($json)){
				$json = json_encode($json);
			}

			$json->time;
			$json->token;
			$json->messages->createdChat[0]->id;
			$json->messages->createdChat[0]->channel;
			$json->messages->createdChat[0]->situation;
			$json->messages->createdChat[0]->department;
			$json->messages->createdChat[0]->company->id;
		}
	}
	/**
	 * [closedChat Atendimento fechado]
	 * @param  string $paramentros [description]
	 * @return [type]              [description]
	 */
	public function closedChat($json = ''){

		$retorno = new stdClass;

		if(!empty($json)){
			if(!is_object($json)){
				$json = json_encode($json);
			}

			$retorno->cd_huggy 		= $json->messages->closedChat[0]->id;
			$retorno->cd_canal 		= $json->messages->closedChat[0]->channel;
			$retorno->dt_fechamento = $json->messages->closedChat[0]->closed_at;
			$retorno->cd_tabulacao  = $json->messages->closedChat[0]->tabulation->id;
			$retorno->nm_tabulacao  = $json->messages->closedChat[0]->tabulation->name;
		}

		return $retorno;
	}
	/**
	 * [receivedMessage description]
	 * @param  string $paramentros [description]
	 * @return [type]              [description]
	 */
	public function receivedMessage($json = ''){

		if(!empty($json)){
			if(!is_object($json)){
				$json = json_encode($json);
			}

			$json->time;
			$json->token;
			$json->messages->receivedMessage[0]->id;
			$json->messages->receivedMessage[0]->body;
			$json->messages->receivedMessage[0]->is_internal;
			$json->messages->receivedMessage[0]->is_email;
			$json->messages->receivedMessage[0]->senderType;
			$json->messages->receivedMessage[0]->receiver;
			$json->messages->receivedMessage[0]->receiverType;
			$json->messages->receivedMessage[0]->file;
			$json->messages->receivedMessage[0]->channel;
			$json->messages->receivedMessage[0]->situation;
			$json->messages->receivedMessage[0]->department;
			$json->messages->receivedMessage[0]->send_at;
			$json->messages->receivedMessage[0]->read_at;

			$json->messages->receivedMessage[0]->company->id;
			/**
			 * Rementente
			 */
			$json->messages->receivedMessage[0]->sender->id;
			$json->messages->receivedMessage[0]->sender->name;
			$json->messages->receivedMessage[0]->sender->mobile;
			$json->messages->receivedMessage[0]->sender->phone;
			$json->messages->receivedMessage[0]->sender->email;
			$json->messages->receivedMessage[0]->sender->photo;
			/**
			 * Cliente
			 */
			$json->messages->receivedMessage[0]->customer->id;
			$json->messages->receivedMessage[0]->customer->name;
			$json->messages->receivedMessage[0]->customer->mobile;
			$json->messages->receivedMessage[0]->customer->phone;
			$json->messages->receivedMessage[0]->customer->email;
			$json->messages->receivedMessage[0]->customer->photo;
			/**
			 * Chat
			 */
			$json->messages->receivedMessage[0]->chat->id;
			$json->messages->receivedMessage[0]->chat->channel;
			$json->messages->receivedMessage[0]->chat->situation;
			$json->messages->receivedMessage[0]->chat->department;

		}
	}
	/**
	 * [answeredChatForm description]
	 * @param  string $paramentros [description]
	 * @return [type]              [description]
	 */
	public function answeredChatForm($json = ''){
		$retorno = new stdClass;

		if(!empty($json)){

			if(!is_object($json)){
				$json = json_encode($json);
			}

			$retorno->id 	     = $json->messages->answeredChatForm[0]->chat->id;
			$retorno->channel    = $json->messages->answeredChatForm[0]->chat->channel;

			$retorno->cd_cliente = $json->messages->answeredChatForm[0]->customer->id;
			$retorno->nm_cliente = $json->messages->answeredChatForm[0]->customer->name;
			$retorno->telefone   = $json->messages->answeredChatForm[0]->customer->mobile;
			$retorno->email      = $json->messages->answeredChatForm[0]->customer->email;

			$perguntas 			 = $json->messages->answeredChatForm[0]->answers;
			$total 				 = count($perguntas);
			$retorno->perguntas  = array();

			for($i = 0; $i < $total; $i++) { 
				$pergunta = new stdClass;
				$pergunta->dm_tipo     = $perguntas[$i]->question->type;
				$pergunta->tx_pergunta = $perguntas[$i]->question->fieldName;
				$pergunta->vl_pergunta = $perguntas[$i]->text;
				$retorno->perguntas[]  = $pergunta;
			}

		}

		return $retorno;
	}	
	/**
	 * [createdCustomer description]
	 * @param  string $paramentros [description]
	 * @return [type]              [description]
	 */
	public function createdCustomer($json = ''){
		if(!empty($json)){
			if(!is_object($json)){
				$json = json_encode($json);
			}

			$json->time;
			$json->token;
			$json->messages->createdCustomer[0]->id;
			$json->messages->createdCustomer[0]->channel;
			$json->messages->createdCustomer[0]->situation;
			$json->messages->createdCustomer[0]->department;
			$json->messages->createdCustomer[0]->company->id;
					}
	}	
	/**
	 * [startedWidgetAttendance description]
	 * @param  string $paramentros [description]
	 * @return [type]              [description]
	 */
	public function startedWidgetAttendance($json = ''){
		if(!empty($json)){
			if(!is_object($json)){
				$json = json_encode($json);
			}

			$json->time;
			$json->token;
			$json->messages->startedWidgetAttendance[0]->id;
			$json->messages->startedWidgetAttendance[0]->channel;
			$json->messages->startedWidgetAttendance[0]->situation;
			$json->messages->startedWidgetAttendance[0]->department;
			$json->messages->startedWidgetAttendance[0]->company->id;
					}
	}
	/**
	 * [updatedLeadByAgent description]
	 * @param  string $paramentros [description]
	 * @return [type]              [description]
	 */
	public function updatedLeadByAgent($json = ''){
		if(!empty($json)){
			if(!is_object($json)){
				$json = json_encode($json);
			}

			$json->time;
			$json->token;
			$json->messages->updatedLeadByAgent[0]->id;
			$json->messages->updatedLeadByAgent[0]->channel;
			$json->messages->updatedLeadByAgent[0]->situation;
			$json->messages->updatedLeadByAgent[0]->department;
			$json->messages->updatedLeadByAgent[0]->company->id;
		}
	}
	/**
	 * [updatedLeadByLead description]
	 * @param  string $paramentros [description]
	 * @return [type]              [description]
	 */
	public function updatedLeadByLead($paramentros = ''){
		if(!empty($json)){
			if(!is_object($json)){
				$json = json_encode($json);
			}

			$json->time;
			$json->token;
			$json->messages->updatedLeadByAgent[0]->id;
			$json->messages->updatedLeadByAgent[0]->channel;
			$json->messages->updatedLeadByAgent[0]->situation;
			$json->messages->updatedLeadByAgent[0]->department;
			$json->messages->updatedLeadByAgent[0]->company->id;
		}
	}
	/**
	 * [GetAgente description]
	 * @param string $cd_agente [description]
	 */
	public function GetAgente($cd_agente = ''){

		$param = 'agents/';
		if(!empty($cd_agente)){
			$param .= $cd_agente;
		}

		$obj = $this->GetDados( $param );
		return $obj;
	}
	/**
	 * [AddAgente description] 
	 * 		1: Agente 2: Gerente 3: Administrador
	 *    "name": "Peter Parker",
		  "phone": "5575988887777",
		  "login": "peterparker",
		  "email": "peterparker@email.com",
		  "password": "12345678",
		  "type": 1
	 * @param string $paramentros [description]
	 */
	public function AddAgente($paramentros = ''){

		if(!isset($paramentros->type)){
			$paramentros->type = 1;
		}

		$obj = $this->PostDados( 'agents', $paramentros );
		return $obj;
	}

	/**
	 * [UpdateAgente description]
	 * @param string $cd_agente   [description]
	 * @param string $paramentros [description]
	 */
	public function UpdateAgente($cd_agente = '', $paramentros = ''){

		$param = 'agents/'.$cd_agente;
		$obj   = $this->PutDados($param, $paramentros);
		return $obj;
	}

	/**
	 * [GetContato description]
	 * @param string $cd_contato [description]
	 */
	public function GetContato($cd_contato = 0, $page = 0){

		$param = 'contacts/';
		if(!empty($cd_contato)){
			$param .= $cd_contato;
		}

		if(!empty($page)){
			$param .= '?page='.$page;
		}

		$obj = $this->GetDados( $param );
		return $obj;
	}
	/**
	 * [AddContato description]
	 * {
		  "name": "Chet Faker",
		  "phone": "5575988887777",
		  "email": "chetfaker@email.com"
		}
	 * @param string $paramentros [description]
	 */
	public function AddContato($paramentros = ''){

		$obj = $this->PostDados( 'contacts', $paramentros );

		if($obj->status){
			$contato = explode('/', $this->headers[9]);
			$obj->cd_contato = intval(array_pop($contato));
			if($obj->cd_contato <= 0){
				$contato = explode('/', $this->headers[10]);
				$obj->cd_contato = intval(array_pop($contato));
			}
		}else{
			if($obj->msn->reason){
				if(isset($obj->msn->reason->email)){
					$obj->msn = "Campo Email obrigatório";
				}elseif(isset($obj->msn->reason->mobile)){
					$obj->msn = "Campo Telefone obrigatório";
				}else{
					$obj->msn = $obj->msn->reason;
				}
			}
		}
		return $obj;
	}

	/**
	 * [UpdateContato description]
	 * @param string $cd_contato  [description]
	 * @param string $paramentros [description]
	 */
	public function UpdateContato($cd_contato = '', $paramentros = ''){

		$param = 'contacts/'.$cd_contato;
		$obj   = $this->PutDados($param, $paramentros);
		return $obj;
	}

	/**
	 * [DeleteContato description]
	 * @param string $cd_contato  [description]
	 * @param string $paramentros [description]
	 */
	public function DeleteContato($cd_contato = ''){

		$param = 'contacts/'.$cd_contato;
		$obj   = $this->DeleteDados($param, $paramentros);
		return $obj;
	}

	/**
	 * [GetAllChats description]
	 */
	public function GetAllChats(){

		$param = 'chats/';
		$obj = $this->GetDados( $param );
		return $obj;
	}

	/**
	 * [GetAllChats description]
	 */
	public function GetMSNChat($cd_chat = ''){

		$param = 'chats/'.$cd_chat.'/messages';
		$obj   = $this->GetDados( $param );
		$chat  = array();
		if($obj->status){
			$total = count($obj->dados);
			for ($i=0; $i < $total; $i++) { 
				$mensagem = new stdClass;
				$mensagem->cd_huggy  	= $obj->dados[$i]->chat->id;
				$mensagem->cd_canal 	= $obj->dados[$i]->chat->channel;
				$mensagem->interna  	= $obj->dados[$i]->is_internal;
				$mensagem->dt_envio 	= $obj->dados[$i]->send_at;
				$mensagem->dt_visto 	= $obj->dados[$i]->read_at;
				$mensagem->mensagem 	= $obj->dados[$i]->body;
				$mensagem->arquivo 		= $obj->dados[$i]->file;
				$mensagem->dm_tipo_de 	= $obj->dados[$i]->senderType;
				$mensagem->cd_de    	= $obj->dados[$i]->sender->id;
				$mensagem->nm_de    	= $obj->dados[$i]->sender->name;
				$mensagem->dm_tipo_para = $obj->dados[$i]->receiverType;
				$mensagem->cd_para    	= isset($obj->dados[$i]->receiver->id) ? $obj->dados[$i]->receiver->id : '';
				$mensagem->nm_para    	= isset($obj->dados[$i]->receiver->name) ? $obj->dados[$i]->receiver->name : '';
				$chat[] = $mensagem;
			}
			$obj->chat = $chat;
		}

		return $obj;
	}
	/**
	 * [AddChat description] 
			Os canais de atendimento podem ser:
			1: whatsapp
			2: email
			3: phone
			4: telegram
			5: messenger
	 * @param string $paramentros [description]
	 */
	public function AddChat($channel = '2', $paramentros = ''){

		$obj = $this->PostDados( 'chats/', $paramentros );
		if($obj->status){
			$contato = explode('/', $this->headers[9]);
			$obj->cd_contato = intval(array_pop($contato));
		}
		return $obj;
	}

	/**
	 * [CloseChat description]
	 * @param string $cd_chat [description]
	 */
	public function CloseChat($cd_chat = ''){
		$paramentros = new stdClass;
		$paramentros->sendFeedback = TRUE;
		$param = 'chats/'.$cd_chat.'/close';
		$obj = $this->PostDados( $param, $paramentros );

		return $obj;
	}

	/**
	 * [ReOpenChat description]
	 * @param string $cd_chat [description]
	 */
	public function ReOpenChat($cd_chat = ''){
		$paramentros = new stdClass;
		$param = 'chats/'.$cd_chat.'/reopen';
		$obj = $this->PostDados( $param, $paramentros );

		return $obj;
	}

	/**
	 * [FilaChat description] 
	 * @param string $cd_chat [description]
	 */
	public function FilaChat($cd_chat = ''){
		$paramentros = new stdClass;
		$param = 'chats/'.$cd_chat.'/queue';
		$obj = $this->PostDados( $param, $paramentros );

		return $obj;
	}

	/**
	 * [FilaChat description]
	 * @param string $cd_chat [description]
	 */
	public function AddChatForm($cd_chat = '', $cd_chatform = 0){
		$paramentros = new stdClass;
		$paramentros->chatform = $cd_chatform;
		$param = 'chats/'.$cd_chat.'/chatform';
		$obj = $this->PostDados( $param, $paramentros );

		return $obj;
	}

	/**
	 * [EnviaMSNChat description]
	 * @param string $cd_chat [description]
	 */
	public function EnviaMSNChat($cd_chat = '', $text = ''){
		$paramentros = new stdClass;
		$paramentros->text = $text;
		$param = 'chats/'.$cd_chat.'/messages';
		$obj = $this->PostDados( $param, $paramentros );

		return $obj;
	}

	/**
	 * [EnviaMSNChat description]
		O tipo de status pode ser:
		1: auto
		2: wait_for_chat
		3: in_chat
		4: blocked
	 * @param string $cd_chat [description]
	 */
	public function AddStatusChat($cd_chat = '', $cd_status = 'auto'){
		$paramentros = new stdClass;
		$paramentros->situation = $cd_status;
		$param = 'chats/'.$cd_chat.'/situation';
		$obj = $this->PostDados( $param, $paramentros );

		return $obj;
	}
	/**
	 * [AddDepartamentChat description]
	 * @param string $cd_chat         [description]
	 * @param string $cd_departamento [description]
	 */
	public function AddDepartamentChat($cd_chat = '', $cd_departamento = ''){
		$paramentros = new stdClass;
		$paramentros->department = $cd_departamento;
		$param = 'chats/'.$cd_chat.'/department';
		$obj = $this->PostDados( $param, $paramentros );

		return $obj;
	}

	/**
	 * [SalvaLog description]
	 * @param [type] $log [description]
	 */
	public static function SalvaLog( $log ){    
        
        $table = '';

        try{
            $connString = sprintf('%s:host=%s dbname=%s user=%s password=%s',
                                    Huggy::$driver_log,
                                    Huggy::$server_log,
                                    Huggy::$database_log,
                                    Huggy::$username_log,
                                    Huggy::$password_log);

            $CONEXAO = new PDO( $connString );
            $CONEXAO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $CONEXAO->setAttribute(PDO::ATTR_TIMEOUT, 12);
            
            if(isset($log->messages->receivedMessage)){
            	$table = 'receivedMessage';
            }
            if(isset($log->messages->createdChat)){
            	$table = 'createdChat';
            }
            if(isset($log->messages->closedChat)){
            	$table = 'closedChat';
            }
            if(isset($log->messages->answeredChatForm)){
            	$table = 'answeredChatForm';
            } 
            if(isset($log->messages->createdCustomer)){
            	$table = 'createdCustomer';
            } 
            if(isset($log->messages->startedWidgetAttendance)){
            	$table = 'startedWidgetAttendance';
            }
            if(isset($log->messages->updatedLeadByAgent)){
            	$table = 'updatedLeadByAgent';
            }
            if(isset($log->messages->updatedLeadByLead)){
            	$table = 'updatedLeadByLead';
            }

            if($table){   
            	
            	$log = json_encode( $log );

				$result = $CONEXAO->query( 'SELECT json FROM "'.$table.'" WHERE json = \'$log\' LIMIT 1' );
				$cavalo = $result->fetchAll( PDO::FETCH_ASSOC );
			    if($cavalo){
			    	return '';
			    }
			    $result = $CONEXAO->query('INSERT INTO "'.$table.'" (json) VALUES (\'$log\')');
				$CONEXAO = null;
			}
        }catch (PDOexception $e) {
            return $e->getMessage();
        }

        return $table;
    }

    /**
	 * [LogErroCPF description]
	 * @param [type] $log [description]
	 */
	public static function LogErroCPF( $id_chat = 0, $cpfcnpj = '', $json = ''){    
    
        try{
            $connString = sprintf('%s:host=%s dbname=%s user=%s password=%s',
                                    Huggy::$driver_log,
                                    Huggy::$server_log,
                                    Huggy::$database_log,
                                    Huggy::$username_log,
                                    Huggy::$password_log);

            $CONEXAO = new PDO( $connString );
            $CONEXAO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $CONEXAO->setAttribute(PDO::ATTR_TIMEOUT, 12);            
            if($CONEXAO){
            	$json = json_encode( $json );
				$result = $CONEXAO->query("INSERT INTO tentativas_cpfcnpj(id_chat, cpfcnpj, json)
	    														  VALUES ($id_chat, '$cpfcnpj', '$json' )");
			}
			$CONEXAO = null;
        }catch (PDOexception $e) {
            return $e->getMessage();
        }

        return TRUE;
    }

    /**
	 * [NrTentavivasCPF description]
	 * @param [type] $log [description]
	 */
	public static function NrTentavivasCPF( $id_chat = 0){    
    	
    	$nr_tentativas = 2;
        try{
            $connString = sprintf('%s:host=%s dbname=%s user=%s password=%s',
                                    Huggy::$driver_log,
                                    Huggy::$server_log,
                                    Huggy::$database_log,
                                    Huggy::$username_log,
                                    Huggy::$password_log);

            $CONEXAO = new PDO( $connString );
            $CONEXAO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $CONEXAO->setAttribute(PDO::ATTR_TIMEOUT, 8);  

            if($CONEXAO){
				$result  = $CONEXAO->query( "SELECT id_chat FROM tentativas_cpfcnpj WHERE id_chat = $id_chat ");
				$dados   = $result->fetchAll( PDO::FETCH_ASSOC );
				$nr_tentativas = count($dados);
			}
			$CONEXAO = null;
        }catch (PDOexception $e) {
            return $e->getMessage();
        }

        return $nr_tentativas;
    }
    /**
     * [LocalizaCPF description]
     * @param string $CPF [description]
     */
    public function LocalizaCPF( $CPF = '' ){  
    	$dados = NULL;
    	$CPF = preg_replace( '/[^0-9]/', '', $CPF);

    	if(empty($CPF)){
    		return $dados;
    	}

    	try{
	    	$CONEXAO = $this-> Conetar();
		    
		    if(!$CONEXAO){
			    return $dados;
			}

		    $STH = $CONEXAO->prepare( 'SELECT * FROM tabela WHERE cpf = :cpf');
		    $STH->bindValue(":cpf", $CPF, PDO::PARAM_STR);
		    $STH->execute();
		    $dados = $STH->fetchAll( PDO::FETCH_ASSOC );
		    if($dados){
		    	$dados = (object) $dados[0];
		    }
		    $STH     = NULL;
		    $CONEXAO = NULL;
	    }catch (PDOexception $e) {
            return $e->getMessage();
        }

		return $dados;
	}

	/**
	 * [CurlInit description]
	 */
	private function CurlInit(){

		if(gettype($this->curl) == 'resource' ){
			curl_close($this->curl);
		}

		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->curl, CURLOPT_HEADER, TRUE);
		curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($this->curl, CURLOPT_TIMEOUT, $this->timeout);
		
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
			"X-Authorization: Bearer " . $this->token,
			"Content-Type: application/json",
		));
		
	}

	/**
	 * [Send description]
	 */
	private function Send(){

		$output 	 	 = curl_exec($this->curl);
		$header_size 	 = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
		$this->http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		$curl_error   	 = curl_error($this->curl);

		curl_close($this->curl);

		$this->SetHeader( substr($output, 0, $header_size) );
		$this->body  = json_decode( substr($output, $header_size) );	

		$obj = new stdClass;
		$obj->status = TRUE;
		$obj->msn    = $this->http_status_msn;
		$obj->dados  = $this->body;
		if($this->http_code == 0){
			$obj->status = FALSE;
			$obj->msn    = $curl_error;
		}
		if($this->http_code >= 400){
			$obj->status = FALSE;
			$obj->msn    = $this->body;
		}

		return $obj;
	}
	/**
	 * [GetDados description]
	 * @param string $paramentros [description]
	 */
	private function GetDados($paramentros = ''){

		$this->CurlInit();
		curl_setopt($this->curl, CURLOPT_HTTPGET, TRUE);
		curl_setopt($this->curl, CURLOPT_URL, $this->api . $paramentros );

		return $this->Send();
	}

	/**
	 * [PostDados description]
	 * @param string $paramentros [description]
	 * @param stdClass $post        [description]
	 */
	private function PostDados($paramentros = '', $post = ''){

		$this->CurlInit();
		curl_setopt($this->curl, CURLOPT_POST, TRUE);
		curl_setopt($this->curl, CURLOPT_URL, $this->api . $paramentros );
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode((array)$post));

		return $this->Send();
	}

	/**
	 * [DeleteDados description]
	 * @param string $paramentros [description]
	 * @param stdClass $put        [description]
	 */
	private function DeleteDados($paramentros = ''){
		$this->CurlInit();
		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($this->curl, CURLOPT_URL, $this->api . $paramentros );
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, "{}");

		return $this->Send();
	}

	/**
	 * [PutDados description]
	 * @param string $paramentros [description]
	 * @param stdClass $put        [description]
	 */
	private function PutDados($paramentros = '', $put = ''){
		$this->CurlInit();
		curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($this->curl, CURLOPT_URL, $this->api . $paramentros );
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode((array)$put));

		return $this->Send();
	}

	/**
	 * [$string description]
	 * @var string
	 */
	private function SetHeader($string = ''){

		$header = array_filter(array_map('trim', explode("\r\n", $string)));
        array_shift($header);
        $this->headers = $header;
		$this->http_status_msn  = $this->GetHttpStatusMensagem( $this->http_code );
	}

	/**
	 * [GetHttpStatusMensagem description]
	 * @param string $code [description]
	 */
	private function GetHttpStatusMensagem($code = ''){
		$msn = '';
		switch (intval($code)){
			case 200: $msn = 'Sucesso';	break;
			case 201: $msn = 'Criado com sucesso'; break;
			case 204: $msn = 'Atualizado com sucesso'; break;
			case 400: $msn = 'O pedido não foi entendido'; break;
			case 400: $msn = 'O pedido não foi entendido'; break;
			case 401: $msn = 'Falha na autenticação'; break;
			case 404: $msn = 'O recurso não foi encontrado.'; break;
			case 501: $msn = 'Falha algo está incorreto';break;
		}
		return $msn;
	}	

	/**
	 * [GetHeader description]
	 */
	public function GetHeader(){
		return $this->headers;
	}

	/**
	 * [GetBody description]
	 */
	public function GetBody(){
		return $this->body;
	}

    /**
     * [Conetar description]
     */
	private function Conetar( ){    
 
        try{
        	$CONEXAO = null;
            $connString = sprintf('%s:host=%s dbname=%s user=%s password=%s',
                                    Huggy::$driver_db,
                                    Huggy::$server_db,
                                    Huggy::$database_db,
                                    Huggy::$username_db,
                                    Huggy::$password_db);

            $CONEXAO = new PDO( $connString );
            $CONEXAO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $CONEXAO->setAttribute(PDO::ATTR_TIMEOUT, 12);
        }catch (PDOexception $e) {
            return $e->getMessage();
        }

        return $CONEXAO;
    }

    /**
     * [RetiraAcentos description]
     * @param string $string [description]
     */
    public static function RetiraAcentos($string = ''){
		$string = str_ireplace("á", "a", $string);
		$string = str_ireplace("ã", "a", $string);
		$string = str_ireplace("â", "a", $string);
		$string = str_ireplace("é", "e", $string);
		$string = str_ireplace("ê", "e", $string);
		$string = str_ireplace("í", "i", $string);
		$string = str_ireplace("î", "i", $string);
		$string = str_ireplace("ó", "o", $string);
		$string = str_ireplace("õ", "o", $string);
		$string = str_ireplace("ô", "o", $string);
		$string = str_ireplace("ú", "u", $string);
		$string = str_ireplace("û", "u", $string);
		$string = str_ireplace("ç", "c", $string);
		$string = str_ireplace("Á", "A", $string);
		$string = str_ireplace("Ã", "A", $string);
		$string = str_ireplace("Â", "A", $string);
		$string = str_ireplace("É", "E", $string);
		$string = str_ireplace("Ê", "E", $string);
		$string = str_ireplace("Í", "I", $string);
		$string = str_ireplace("Î", "I", $string);
		$string = str_ireplace("Ó", "O", $string);
		$string = str_ireplace("Õ", "O", $string);
		$string = str_ireplace("Ô", "O", $string);
		$string = str_ireplace("Ú", "U", $string);
		$string = str_ireplace("Û", "U", $string);
		$string = str_ireplace("Ç", "C", $string);
		return $string;
	} 
}