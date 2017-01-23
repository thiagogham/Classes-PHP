<?
/**
 * Descrição: Implementa Classe de Funçoes para uso do PHP com SSH
 * Autor: Thiago R. Gham
 * Data: 29/01/2015
 * Versão: 1.0
 */
class SSH{
	/**
	 * [$servidor description]
	 * @var string
	 */
	private $servidor  = 'localhost';
	/**
	 * [$usuario description]
	 * @var string
	 */
	private $usuario   = 'root';
	/**
	 * [$senha description]
	 * @var string
	 */
	private $senha     = 'root';
	/**
	 * [$porta description]
	 * @var integer
	 */
	private $porta     = 22;
	/**
	 * [$conexao description]
	 * @var boolean
	 */
	public	$conexao   = false;
	
	/**
	 * [__construct description]
	 * @param [type] $servidor [description]
	 * @param [type] $usuario  [description]
	 * @param [type] $senha    [description]
	 * @param [type] $porta    [description]
	 */
	public function __construct($servidor, $usuario, $senha, $porta) {
		$this->servidor  = $servidor;
		$this->usuario   = $usuario;
		$this->senha     = $senha;
		$this->porta     = $porta;
		if(!function_exists("ssh2_connect")) {
			return false;
		}
		$retorno = $this->AbreConexao();

		return $retorno;
	}
	/**
	 * [AbreConexao description]
	 */
	public function AbreConexao(){
		$this->conexao = @ssh2_connect($this->servidor, $this->porta);
		if(!$this->conexao){
			return false;
		}elseif(!@ssh2_auth_password($this->conexao, $this->usuario, $this->senha)){
			return false;
		}else{
			return true;
		}
	}
	/**
	 * [FechaConexao description]
	 */
	function FechaConexao(){
		@ftp_close($this->conexao);
	}
	/**
	 * [AbreShell description]
	 */
	function AbreShell() {
		$ponteiro_shell = @ssh2_shell($this->conexao, 'xterm');
		if(!$ponteiro_shell) {
			return false;
		}else {
			$this->current_shell = $ponteiro_shell;
			return $ponteiro_shell;
		}
	}
	/**
	 * [ExecutaShell description]
	 * @param [type] $ponteiro_shell [description]
	 * @param [type] $comando        [description]
	 */
	function ExecutaShell($ponteiro_shell, $comando) {
		if(!(@fwrite($ponteiro_shell,"$comando"."\r\n"))) {
			return false;
		}else {
     		return true;
		}
	}
	/**
	 * [ExecutaComando description]
	 * @param [type] $comando [description]
	 */
	function ExecutaComando($comando){
		if(!($stream = @ssh2_exec($this->conexao, $comando))){
			return false;
		}else {
      		$this->current_stream = $stream;
			$stream = $this->RetornoComando($stream);
			return $stream;
		}
	}
	/**
	 * [RetornoComando description]
	 * @param [type] $stream [description]
	 */
	function RetornoComando($stream){
		if(!stream_set_blocking($stream, true)){
			return false;
		}else{
			$data = '';
			while( $buf = fread($stream,4096) ){
				$data .= $buf;
			}
			fclose($stream);
			return $data;
		}
	}
	/**
	 * [RetornaArquivo description]
	 * @param [type] $arquivoRemoto [description]
	 * @param [type] $arquivoLocal  [description]
	 */
	function RetornaArquivo($arquivoRemoto, $arquivoLocal){
		return  @ssh2_scp_recv($this->conexao, $arquivoRemoto, $arquivoLocal);
	}
	/**
	 * [EnviaArquivo description]
	 * @param [type]  $arquivoLocal  [description]
	 * @param [type]  $arquivoRemoto [description]
	 * @param integer $permissao     [description]
	 */
	function EnviaArquivo($arquivoLocal, $arquivoRemoto, $permissao = 0644){
		return @ssh2_scp_send($this->conexao, $arquivoLocal, $arquivoRemoto, $permissao);
	}
	/**
	 * [RemoveArquivo description]
	 * @param [type] $arquivoRemoto [description]
	 */
	function RemoveArquivo($arquivoRemoto){
		if (!@ssh2_sftp_unlink($this->conexao, $arquivoRemoto)) {
			if(!unlink("ssh2.sftp://$this->conexao$arquivoRemoto")){
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
	/**
	 * [CriaDiretorio description]
	 * @param [type] $diretorio [description]
	 */
	function CriaDiretorio($diretorio){
		if (!@ssh2_sftp_mkdir($this->conexao, $diretorio)) {
			if(!mkdir('ssh2.sftp://' . $this->conexao . $diretorio, 0777)){
				return false;
			}else{
				return true;
			}
		}else{
			return true;
		}
	}
	/**
	 * [RemoveDiretorio description]
	 * @param [type] $diretorioRemoto [description]
	 */
	function RemoveDiretorio($diretorioRemoto){
		if (!@ssh2_sftp_rmdir($this->conexao, $diretorioRemoto)) {
			$files = array_diff(scandir('ssh2.sftp://' . $this->conexao . $diretorioRemoto), array('.','..'));
			foreach ($files as $file) {
				(is_dir('ssh2.sftp://' . $this->conexao . $diretorioRemoto."/$file")) ? $this->RemoveDiretorio("$diretorioRemoto/$file") : unlink('ssh2.sftp://' . $this->conexao . $diretorioRemoto."/$file");
			}
			return rmdir('ssh2.sftp://' . $this->conexao . $diretorioRemoto);
		}else{
			return true;
		}
	}
	/**
	 * [RetornaDiretorio description]
	 * @param [type] $diretorioLocal  [description]
	 * @param [type] $diretorioRemoto [description]
	 */
	function RetornaDiretorio($diretorioLocal, $diretorioRemoto){
		if(!mkdir($diretorioLocal,0777)){
			return false;
		}
		chmod($diretorioLocal,0777); 
		$files = array_diff(scandir('ssh2.sftp://' . $this->conexao . $diretorioRemoto), array('.','..'));
		if(!empty($files)){
			$entrou = false;
			foreach ($files as $file) {
				$entrou = true;
				@ssh2_scp_recv($this->conexao, "$diretorioRemoto/$file", "$diretorioLocal/$file");
			}
			return $entrou;
		}else{
			return false;
		}
	}
	/**
	 * [EnviaDiretorio description]
	 * @param [type] $diretorioLocal  [description]
	 * @param [type] $diretorioRemoto [description]
	 * @param [type] $permissao       [description]
	 */
	function EnviaDiretorio($diretorioLocal, $diretorioRemoto, $permissao){
		$this->CriaDiretorio($diretorioRemoto);
		$files = array_diff(scandir($diretorioLocal), array('.','..'));
		if(!empty($files)){
			$entrou = false;
			foreach ($files as $file) {
				$entrou = true;
				$this->EnviaArquivo("$diretorioLocal/$file", "$diretorioRemoto/$file", 0777);
			}
			return $entrou;
		}else{
			return false;
		}
	}
}