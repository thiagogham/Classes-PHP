<?
/**
 * Descrição: Classe para operações em servidor PRTG
 * Autor: Thiago R. Gham
 * Data: 05/04/2015
 * Versão: 1.0
 */
class PRTG{
	/**
	 * [$debug description]
	 * @var boolean
	 */
	public  $debug 				= false;
	/**
	 * [$url description]
	 * @var string
	 */
	private $url 			 	= 'http://localhost';
	/**
	 * [$table description]
	 * @var string
	 */
	private $table 			 	= 'api/table.';
	/**
	 * [$graph description]
	 * @var string
	 */
	private $graph 			 	= 'chart.png';
	/**
	 * [$duplicateobject description]
	 * @var string
	 */
	private $duplicateobject 	= 'api/duplicateobject.htm';
	/**
	 * [$setobjectproperty description]
	 * @var string
	 */
	private $setobjectproperty 	= 'api/setobjectproperty.htm';
	/**
	 * [$pause description]
	 * @var string
	 */
	private $pause 			 	= 'api/pause.htm';
	/**
	 * [$discovernow description]
	 * @var string
	 */
	private $discovernow     	= 'api/discovernow.htm';
	/**
	 * [$delete description]
	 * @var string
	 */
	private $delete 			= 'api/deleteobject.htm';
	/**
	 * [$sensorstats description]
	 * @var string
	 */
	private $sensorstats		= 'controls/sensorstats.htm';
	/**
	 * [$username description]
	 * @var string
	 */
	private $username		 	= 'prtgapi';
	/**
	 * [$password description]
	 * @var string
	 */
	private $password 		 	= 'prtgapi';
	/**
	 * [$dados description]
	 * @var string
	 */
	private $dados	    	 	= '';
	/**
	 * [$content description]
	 * @var string
	 */
	public $content 		 	= 'sensors';
	/**
	 * [$columns description]
	 * @var string
	 */
	public $columns 		 	= 'objid,name,type,group,device,host,sensor,status,downtimetime,lastvalue,lastcheck,message,location,downtimesince';
	/**
	 * [$sortby description]
	 * @var string
	 */
	public $sortby  			= 'downtimetime';
	/**
	 * [$filters description]
	 * @var stdClass
	 */
	public $filters    			= array();
	/**
	 * Using multiple filter_status fields performs a logical OR. 	
	 * Unknown=1, Collecting=2, Up=3, Warning=4, Down=5, NoProbe=6, PausedbyUser=7, 
	 * PausedbyDependency=8, PausedbySchedule=9, Unusual=10, PausedbyLicense=11, 
	 * PausedUntil=12, DownAcknowledged=13, DownPartial=14
	 * @var array
	 */
	public $filter_status 		= array();
	/**
	 * [$filter_type description]
	 * @var string
	 */
	public $filter_type 		= '';
	/**
	 * output 	Controls the output format 	"xml": default format (recommended)
	 *	"xmltable": a HTML table in XML format, "csvtable": comma separated format, "html": HTML table
	 * @var string
	 */	
	public $type 				= 'json';
	/**
	 * [$FilterWarning description]
	 * @var integer
	 */
	public static $FilterWarning 	= 4;
	/**
	 * [$FilterDown description]
	 * @var integer
	 */
	public static $FilterDown		= 5;
	/**
	 * [$FilterUnusual description]
	 * @var integer
	 */
	public static $FilterUnusual	= 10;
	/**
	 * [$SSHSenha description]
	 * @var string
	 */
	public static $SSHSenha 		= 'linuxloginpassword';
	/**
	 * [__construct description]
	 * @param [type] $username [description]
	 * @param [type] $password [description]
	 */
	function __construct($username = '', $password = '', $url = '', $type = 'json'){

		if(!empty( $username )){
			$this->username = $username;
		}

		if(!empty( $password )){
			$this->password = $password;
		}

		if(!empty( $url )){
			$this->url = $url;
		}

		if(!empty( $type )){
			$this->type = $type;
		}
	}
	/**
	 * [GetDadosjson description]
	 */
	private function GetDadosJson( $dados = ''){
		if(empty($dados)){
			return json_decode( $this->dados );	
		}else{
			return json_decode( $dados );
		}
		
	}
	/**
	 * [GetDadosxml description]
	 */
	private function GetDadosXML(){
		return simplexml_load_string( $this->dados );
	}
	/**
	 * [GetParamentros description]
	 * @param [type] $parametros [description]
	 */
	private function GetParamentros( $parametros ){
		return http_build_query( $parametros ) ;
	}
	/**
	 * [GetFile description]
	 * @param [type] $url [description]
	 */
	private function GetFile( $url ){
		if($this->debug){
			echo "$url /n<br>";
		}
		return file_get_contents( $url );
	}
	/**
	 * [GetUrl description]
	 * @param [type] $parametros [description]
	 */
	private function GetUrl($api, $parametros = array()){
		
		$parametros['username'] = $this->username;
		$parametros['password'] = $this->password;

		$url = urldecode( "{$this->url}{$api}?" . $this->GetParamentros( $parametros ) );

		return 	$url;
	}
	/**
	 * [GetDados description]
	 */
	public function GetDados(){
		
		$sensor_url = $this->GetUrl( $this->table.$this->type, $this->GetDadosParamentros() );

		$this->dados =  $this->GetFile( $sensor_url );
		switch (strtoupper( $this->type ) ) {
			case 'JSON':
				return $this->GetDadosJson();
				break;
			case 'XML':
				return $this->GetDadosXML();
				break;
			default:
				return $this->GetDadosJson();
				break;
		}
	}
	/**
	 * [GetDadosParamentros description]
	 */
	private function GetDadosParamentros (){
		$parametros = array(
		    'content'		=> $this->content,
		    'columns'		=> $this->columns
		);

		if(!empty( $this->sortby )){
			$parametros['sortby'] = $this->sortby;
		}

		if(count( $this->filter_status ) > 0){
			if( is_array( $this->filter_status ) ){
				$this->filter_status = implode('filter_status=', $this->filter_status);
				$this->filter_status = str_ireplace('filter_status', '&filter_status', $this->filter_status);
			}
			$parametros['filter_status'] = $this->filter_status;
		}

		if(!empty( $this->filter_type )){
			$parametros['filter_type'] = $this->filter_type;
		}

		if(!empty($this->filters)){
			foreach ($this->filters as $filter => $value) {
				if(strrchr($filter, 'filter_')){
					$parametros[$filter] = $value;
				}else{
					$parametros['filter_'.$filter] = $value;
				}
				
			}
		}
		return $parametros;
	}
	/**
	 * [GetLocation description]
	 * @param [type] $name [description]
	 */
	public function GetLocation( $name ){

		$parametros = array(
		    'content' => 'devices',
		    'columns' => 'objid,location,host',
		    'filter_name' => $name
		);

		$location_url = $this->GetUrl( $this->table.$this->type, $parametros );

		$dados =  $this->GetDadosJson( $this->GetFile( $location_url ) );
		
		$retorno = '';
		if(is_object( $dados )){
			$retorno = $dados->devices[0];
		}

		return $retorno;
	}
	/**
	 * [DuplicaObjeto description]
	 * @param [type] $id       [description]
	 * @param [type] $name     [description]
	 * @param [type] $host     [description]
	 * @param [type] $targetid [description]
	 * @param [type] $location [description]
	 */
	public function DuplicaObjeto($id, $name, $host, $targetid){

		$parametros = array('id' 				 => $id,
							'name' 				 => $name,
							'host' 				 => $host,
							'targetid' 			 => $targetid);

		$duplica_url = $this->GetUrl($this->duplicateobject, $parametros);

		$retorno = $this->GetFile( $duplica_url );
		
		$id = $this->RetirarEntre($retorno, '<input id="hiddenloginurl" type="hidden" name="loginurl" value="/device.htm?id=', '">');

		if(intval( $id )){
			return $id;
		}
		$id = $this->RetirarEntre($retorno, '<input id="hiddenloginurl" type="hidden" name="loginurl" value="/group.htm?id=', '">');

		if(intval( $id )){
			return $id;
		}
		return FALSE;
	}
	/**
	 * [RetirarEntre description]
	 * @param [type] $string [description]
	 * @param [type] $inicio [description]
	 * @param [type] $fim    [description]
	 */
	public function RetirarEntre($string, $inicio, $fim){
		$string = stristr($string, $inicio);
		$string = substr($string, strlen($inicio));
		$stop   = stripos($string, $fim);
		$string = substr($string, 0, $stop);
		return $string;
	}
	/**
	 * [AlteraObjeto description]
	 * @param [type] $id    [description]
	 * @param [type] $dados [description]
	 */
	public function AlteraObjeto( $id, $dados){
		
		foreach ($dados as $key => $value) {
			$parametros = array('id' => $id, 'name' => $key, 'value' => $value);
			$altera_url = $this->GetUrl($this->setobjectproperty, $parametros);
			$retorno    = $this->GetFile( $altera_url );
		}

		if(empty($retorno)){
			return FALSE;
		}
		return TRUE;
	}
	
	/**
	 * [GetGraph description]
	 * @param integer $id      [The object ID of the desired graph object (usually the ID of a sensor object)]
	 * @param integer $graphid [Selects graph number (0=live, 1=last 48 hours, 2=30 days, 3=365 days)]
	 * @param integer $width   [Width of the image in pixels]
	 * @param integer $height  [Height of the image in pixels]
	 */
	public function GetGraph($retorno = false, $id = 1, $graphid = 1, $width = 640, $height){

		$parametros = array('type'  	=> 'graph',
							'id' 		=> $id,
							'graphid' 	=> $graphid,
							'width' 	=> $width,
							'height' 	=> empty($height) ? intval($width / 1.77777) : $height );
		
		$graph_url = $this->GetUrl( $this->graph, $parametros );

		$imgPath = "data:image/png;base64," . base64_encode( $this->GetFile( $graph_url ) );
		
		$saida = "<figure style='text-align: center;''>
				  	<img src='$imgPath' alt='$id' title='$id' />	
				  	<figcaption>Graph $id </figcaption>
				  </figure>";
		if($retorno){
			return $saida;
		}else{
			echo $saida;
		}
		
	}
	/**
	 * [StartObjeto description]
	 * @param [type] $id [description]
	 */
	public function StartObjeto( $id ){

		$parametros = array('id' 		=> $id,
							'action' 	=> 1);

		$start_url = $this->GetUrl($this->pause, $parametros);

		$retorno = $this->GetFile( $start_url );

		if(empty($retorno)){
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * [PauseObjeto description]
	 * @param [type] $id [description]
	 */
	public function PauseObjeto( $id ){

		$parametros = array('id' 		=> $id,
							'action' 	=> 0);

		$start_url = $this->GetUrl($this->pause, $parametros);

		$retorno = $this->GetFile( $start_url );

		if(empty($retorno)){
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * [Descobrir description]
	 * @param [type] $id [description]
	 */
	public function Descobrir( $id ){

		$discover_url = $this->GetUrl($this->discovernow, array('id' => $id));

		$retorno = $this->GetFile( $discover_url );

		if(empty($retorno)){
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * [Delete description]
	 * @param [type] $id [description]
	 */
	public function Delete( $id ){

		$delete_url = $this->GetUrl($this->delete, array('id' => $id, 'approve' => 1));

		$retorno = $this->GetFile( $delete_url );

		if(empty($retorno)){
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * [GetSensorStats description]
	 */
	public function GetSensorStats(){

		$html = '<style type="text/css" media="screen">.refreshable{margin:0;padding:0}.refreshable .sensorlinkred{background-color:#D21925}.refreshable .sensorlinkgreen{background-color:#98BD1D}.refreshable .sensorlinkpaused{background-color:#6294C8}.refreshable .sensorlinkpartialred{background-color:#D21925}.refreshable .sensorlinkack{background-color:#E89574}.refreshable .sensorlinkwarn{background-color:#EECE00}.refreshable .sensorlinkunusual{background-color:#EE9804}.refreshable .sensorlinkblack{display:none;background-color:#707172}.refreshable a div.sensx{background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOBAMAAADtZjDiAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpGQzdGMTE3NDA3MjA2ODExOTEwOUQ1ODRBMEIwQzk5MCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo2ODRBRkE4Rjg2NTIxMUUxOUUxQUJBOTU2NjQ4OTkxNSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo2ODRBRkE4RTg2NTIxMUUxOUUxQUJBOTU2NjQ4OTkxNSIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IE1hY2ludG9zaCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5RDU4NEEwQjBDOTkwIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5RDU4NEEwQjBDOTkwIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+XJpsTgAAACpQTFRFAAAA////////////////////////////////////////////////////hrvKLwAAAA10Uk5TAAAQIEBMZHCAn7DP3IoE5M8AAAA6SURBVAjXYxCEAAYE7TlzCpiee/d2IIie5XG3EEQzMEPp6LuOIJrp7hFGEM141xFClzlA+QwoNIr5AGBzEjxQJZGPAAAAAElFTkSuQmCC)}.refreshable a div.sensg{background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAZBAMAAADztQLBAAAAB3RJTUUH2gMWEgEL3Mh4rQAAAAlwSFlzAAALEgAACxIB0t1+/AAAACRQTFRFmL0doMEyqcVEr8hTvtBxxNN+y9iM0duY3+Sz7/La+/ry////uOKS0gAAAC9JREFUCNdjYMAGOKB0J5S7GEJ3B0C4O0Ake2L3BBDNvHqXAlg4egtEFWsCA/0BAA/5B1fOhntkAAAAAElFTkSuQmCC)}.refreshable a{color:#fff!important;height:14px;padding:3px 5px 4px;box-shadow:1px 1px 1px #444;-moz-box-shadow:1px 1px 1px #444;-webkit-box-shadow:1px 1px 1px #444;margin:4px 1px 4px 4px;border-radius:3px;-moz-border-radius:3px;-webkit-border-radius:3px;padding:4px 3px 4px 13px;background-position:1px 3px;background-repeat:no-repeat}.refreshable a div{background:#FFF;border-radius:3px;-moz-border-radius:3px;-webkit-border-radius:3px;box-shadow:1px 1px 1px #444 inset;-moz-box-shadow:1px 1px 1px #444 inset;-webkit-box-shadow:1px 1px 1px #444 inset;color:#000;display:inline-block;padding:1px 3px 0 6px;text-align:center;margin-left:2px;background-image:none!important;opacity:.8}</style>';
		$html .= $this->GetFile(  $this->GetUrl( $this->sensorstats ) );

		$html = str_ireplace(array( 'href="sensors.htm?id=0&filter_status=5"',
									'href="sensors.htm?id=0&filter_status=4"',
									'href="sensors.htm?id=0&filter_status=10"',
									'href="sensors.htm?id=0&filter_status=2&filter_status=3"',
									'href="sensors.htm?id=0&filter_status=7&filter_status=8&filter_status=9&filter_status=11&filter_status=12"',
									'href="sensors.htm?id=0&filter_status=0&filter_status=6&filter_status=1"'),'', $html);	

		$img  = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldC
				BiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjA
				xMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1s
				bnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZ
				G9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpGQzdGMTE3NDA3MjA2ODExOTEwOUQ1ODRBMEIwQzk5MCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo4M0U3QkNCQTg2NTIxMU
				UxOUUxQUJBOTU2NjQ4OTkxNSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo4M0U3QkNCOTg2NTIxMUUxOUUxQUJBOTU2NjQ4OTkxNSIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IE1hY2ludG9zaCI
				+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5RDU4NEEwQjBDOTkwIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5
				RDU4NEEwQjBDOTkwIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+QW00MAAAAD5JREFUeNpi/P//PwM5gImBTEBVjf+hGBd/EDmVKMCCLsAIBGCPQaMJn
				Q9XR814pG2osmARYyTAH6DoAAgwAEuBFBV7y3R0AAAAAElFTkSuQmCC';
		$html = str_ireplace('/icons/led_red_transparent.png', $img, $html);
		
		$img = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCB
				iZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAx
				MC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sb
				nM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG
				9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpGQzdGMTE3NDA3MjA2ODExOTEwOUQ1ODRBMEIwQzk5MCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpDQzZGMjZCRjg2NTIxMUU
				xOUUxQUJBOTU2NjQ4OTkxNSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpDQzZGMjZCRTg2NTIxMUUxOUUxQUJBOTU2NjQ4OTkxNSIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IE1hY2ludG9zaCI+
				IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5RDU4NEEwQjBDOTkwIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5R
				DU4NEEwQjBDOTkwIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+vd+CVQAAAJFJREFUeNpi/P//PwM5gImBTDCwGv9DsQMQCyDxDaAYxgcDFiSNB6CaQIo
				UkMQd0NRgaDwIVWQPxB+QxJH5G2GCjEjRAbLpPBA/gPJhihWgYiB5QyC+APEYUCNMM5B+/x8B+qEYBt4jq0cP1QNI7I3ITgOCDcgKWdA0LoSGKAMjI+MBqA0H0P2H7sdBnnIAAgwASMJOp00SF6oAAAAASUVORK5CYII=';
		$html = str_ireplace('/icons/led_yellow_transparent.png', $img, $html);	

		$img  = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCB
				iZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxM
				C8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM
				6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZ
				S5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpGQzdGMTE3NDA3MjA2ODExOTEwOUQ1ODRBMEIwQzk5MCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDozNUNCQzIxODg2NTIxMUUxOUU
				xQUJBOTU2NjQ4OTkxNSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDozNUNCQzIxNzg2NTIxMUUxOUUxQUJBOTU2NjQ4OTkxNSIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IE1hY2ludG9zaCI+IDx4b
				XBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5RDU4NEEwQjBDOTkwIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5RDU4NEE
				wQjBDOTkwIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+gupWfgAAAH5JREFUeNpi/P//PwM5gImBXACykRhbgWoEgHg+XD0xGoHyCkC8H0oTpxEoZwDE7
				4E4AMWF+DQiaerH8BqMAXIGmqaE/xBwHuQ/fBoToIoKoPg/1DYDrIGJ7FRoqCGDBJyxgKZRAGorCMzHG33ogYMU9AL4NDLSPcmRrREgwACBKOzsVL5O4gAAAABJRU5ErkJggg=='	;
		$html = str_ireplace('/icons/led_green_transparent.png', $img, $html);	

		$img  = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCB
				iZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxM
				C8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM
				6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZ
				S5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpGQzdGMTE3NDA3MjA2ODExOTEwOUQ1ODRBMEIwQzk5MCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDozNUNCQzIxNDg2NTIxMUUxOUU
				xQUJBOTU2NjQ4OTkxNSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDozNUNCQzIxMzg2NTIxMUUxOUUxQUJBOTU2NjQ4OTkxNSIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IE1hY2ludG9zaCI+IDx4b
				XBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5RDU4NEEwQjBDOTkwIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5RDU4NEE
				wQjBDOTkwIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+AcoHVAAAACtJREFUeNpi/P//PwM5gImBTDA4NP6HYlz84eBH2mpkwSLGSIA/1PwIEGAA5VUHG
				UGGBOUAAAAASUVORK5CYII=';
		$html = str_ireplace('/icons/led_blue_transparent.png', $img, $html);	

		$img  = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCB
				iZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxM
				C8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM
				6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZ
				S5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpGQzdGMTE3NDA3MjA2ODExOTEwOUQ1ODRBMEIwQzk5MCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo4M0U3QkNCMjg2NTIxMUUxOUU
				xQUJBOTU2NjQ4OTkxNSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo2ODRBRkE5Njg2NTIxMUUxOUUxQUJBOTU2NjQ4OTkxNSIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IE1hY2ludG9zaCI+IDx4b
				XBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5RDU4NEEwQjBDOTkwIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5RDU4NEE
				wQjBDOTkwIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+n9qYXwAAAFRJREFUeNpi/P//PwM5gImBTEBVjSC3/2dkZGQAYRgfiB0Gn1Npq5EFlwQwfkGB8
				YABjwJ0fP8/JngPxALI6rBp';
		$html = str_ireplace('/icons/led_orange_transparent.png', $img, $html);	

		$img  = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2ZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBi
				ZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8
				wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG
				1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb
				20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDpGQzdGMTE3NDA3MjA2ODExOTEwOUQ1ODRBMEIwQzk5MCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo2ODRBRkE4Rjg2NTIxMUUxOUUxQUJB
				OTU2NjQ4OTkxNSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo2ODRBRkE4RTg2NTIxMUUxOUUxQUJBOTU2NjQ4OTkxNSIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IE1hY2ludG9zaCI+IDx4bXBNTTp
				EZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5RDU4NEEwQjBDOTkwIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkZDN0YxMTc0MDcyMDY4MTE5MTA5RDU4NEEwQjBDOT
				kwIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+XJpsTgAAAIxJREFUeNpi/P//PwM5gImBTMCCRcwBiOPRxBYC8QEUEZBT0fD8/5jgPBD7IKvD5dSNQOwIx
				I1QvgEQq+C1EU1OAcnWAmJsBEn6AKn1SEIXCNoIpAWQbLoDxA7oanFpZEDS6IAmDsYseKKqEIi/gBiMjIwYkozoAQJSBBX7D+UzInuL4pTDSPe0ChBgADy3uh2HihhiAAAAAElFTkSuQmCC';

		$html = str_ireplace('/icons/led_grey_transparent.png', $img, $html);	

		return $html;
	}
}