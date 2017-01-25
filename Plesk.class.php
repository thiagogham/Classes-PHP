<?php
/*
*   Descrição: API Cliente Plesk
*   Autor: Thiago R. Gham
*   Data: 19-01-2017
*   Versão 1.0
*    Exemplo de Uso:
*        $client   = new Plesk();
*        $response = $client->liberar('qualquer.com.br');
*        $response: 
*            ok
*            qualquer.com.br
*            1
**/
class Plesk{
    /**
     * @var type
     */
    private $_host;
    /**
     * @var type
     */
    private $_port;
    /**
     * @var type
     */
    private $_protocol;
    /**
     * @var type
     */
    private $_login;
    /**
     * @var type
     */
    private $_password;
    /**
     * @var type
     */
    private $_secretKey;
    /**
     * @var type
     */
    private $_site = '';
    /**
     * @var type
     */
    private $cd_status = array( 0       => 'STATUS_ACTIVE',
                                1       => 'STATUS_STATUS',
                                2       => 'STATUS_WITH_PARENT',
                                4       => 'STATUS_BACKUP_RESTORE',
                                16      => 'STATUS_ADMIN' ,
                                32      => 'STATUS_RESELLER',
                                64      => 'STATUS_CLIENT',
                                256     => 'STATUS_EXPIRED');
    /**
     * @var type
     */
    private $tx_status = array( 'STATUS_ACTIVE'         => 'Ativo',
                                'STATUS_STATUS'         => 'Bloqueado',
                                'STATUS_WITH_PARENT'    => 'Bloqueado',
                                'STATUS_BACKUP_RESTORE' => 'Bloqueado por backup',
                                'STATUS_ADMIN'          => 'Bloqueado',
                                'STATUS_RESELLER'       => 'Bloqueado pela Revenda',
                                'STATUS_CLIENT'         => 'Bloqueado pelo Cliente',
                                'STATUS_EXPIRED'        => 'Bloqueado Expirado');      
    /**
     * [__construct description]
     * @param [type] $conf [description]
     */
    public function __construct($conf = ''){
        
        if(empty($conf)){
            $this->_host        = 'localhost';
            $this->_login       = 'admin';
            $this->_password    = 'admin';        
            $this->_port        = 8443;
            $this->_protocol    = 'https';
        }else{
            $this->_host        = $conf['host'];
            $this->_login       = $conf['login'];
            $this->_password    = $conf['password'];        
            $this->_port        = empty($conf['port']) ? 8443 : $conf['port'];
            $this->_protocol    = empty($conf['protocol']) ? 'https' : $conf['protocol'];
        }      
    }
    /**
     * [setSecretKey description]
     * @param [type] $secretKey [description]
     */
    public function setSecretKey( $secretKey ){
        $this->_secretKey = $secretKey;
    }
    /**
     * Description
     * @param type|string $site 
     * @return type
     */
    public function setSite( $site = ''){

        $this->_site = $this->filterSite( $site );

        $error = $this->validaSite( );
        if($error->erro){
            print_r($error);
            exit;
        }
    }
    /**
     * [request description] 
     * @param  [type] $request [description]
     * @return [type]          [description]
     */
    public function request($request){

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "$this->_protocol://$this->_host:$this->_port/enterprise/control/agent.php");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeaders());
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        $result  = curl_exec($curl);
        $errno   = curl_errno( $curl );
        $error   = curl_error( $curl );
        curl_close($curl);

        if($errno){
            die ("$error") ;
        }

        return $result;
    }
    /**
     * [getHeaders description]
     * @return [type] [description]
     */
    private function getHeaders(){
        $headers = array(
            "Content-Type: text/xml",
            "HTTP_PRETTY_PRINT: TRUE",
        );

        if ($this->_secretKey) {
            $headers[] = "KEY: $this->_secretKey";
        } else {
            $headers[] = "HTTP_AUTH_LOGIN: $this->_login";
            $headers[] = "HTTP_AUTH_PASSWD: $this->_password";
        }

        return $headers;
    }
    /**
     * Description
     * @return type
     */
    public function addSite( $dados = ''){
        
        if(empty($dados) or !is_array($dados)){
            return false;
        }

        $site    = $dados['site'];
        $ip      = $this->_host;//$dados['host'];
        $usuario = $dados['usuario'];
        $senha   = $dados['senha'];

        $request = '<packet>
                      <webspace>
                        <add>
                          <gen_setup>
                            <name>'.$site.'</name>
                            <ip_address>'.$ip.'</ip_address>
                          </gen_setup>
                          <hosting>
                            <vrt_hst>
                              <property>
                                <name>ftp_login</name>
                                <value>'.$usuario.'</value>
                              </property>
                              <property>
                                <name>ftp_password</name>
                                <value>'.$senha.'</value>
                              </property>
                              <ip_address>'.$ip.'</ip_address>
                            </vrt_hst>
                          </hosting>
                        </add>
                      </webspace>
                    </packet>';

        $dados = simplexml_load_string(rtrim($this->request($request))); 

        $error = $this->validaRequest( $dados );

        if($error->erro){
            return $error;
        }

        $dados = $dados->webspace->add->result;

        $obj = new StdClass;
        $obj->id  = $dados->id;
        $obj->tx_status = $dados->status;
        $obj->guid      = $dados->guid;

        $request = '<packet>
                      <site>
                        <add>
                          <gen_setup>
                            <name>'.$site.'</name>
                            <webspace-id>'.$obj->id.'</webspace-id>
                          </gen_setup>
                        </add>
                      </site>
                    </packet>';

        $dados = simplexml_load_string(rtrim($this->request($request))); 

        $error = $this->validaRequest( $dados );

        if($error->erro){
            return $error;
        }
        
        return $dados;

    }
    /**
     * [getInfo description]
     * @return [type]       [description]
     */
    public function getInfo( ){

        $request ='<packet>
                        <site>
                            <get>
                                <filter>
                                    <name>'.$this->_site.'</name>
                                </filter>
                                <dataset>
                                    <gen_info/>
                                </dataset>
                            </get>
                        </site>
                    </packet>';

        $dados = simplexml_load_string(rtrim($this->request($request))); 

        $error = $this->validaRequest( $dados );

        if($error->erro){
            return $error;
        }

        $dados = $dados->site->get->result->data->gen_info;

        $obj = new StdClass;
        $obj->cd_status = $this->cd_status[(int)$dados->status];
        $obj->tx_status = $this->tx_status[$obj->cd_status];
        $obj->site      = $dados->name;
        $obj->data      = $dados->cr_date;

        return $obj;
    }
    /**
     * [bloquear description]
     * @return [type]       [description]
     */
    public function bloquear( ){
                
        $request = '<packet>
                        <webspace>
                            <set>
                                <filter>
                                    <name>'.$this->_site.'</name>
                                </filter>
                                <values>
                                    <gen_setup>
                                        <status>16</status>
                                    </gen_setup>
                                </values>
                            </set>
                        </webspace>
                    </packet>';
        
        $dados = simplexml_load_string(rtrim($this->request($request))); 

        $error = $this->validaRequest( $dados );

        if($error->erro){
            return $error;
        }

        $dados = $dados->webspace->set->result;

        $obj = new StdClass;
        $obj->id        =  $dados->id;
        $obj->tx_status = $dados->status;
        $obj->site      = $dados->{'filter-id'};

        return $dados;
    }
    /**
     * [liberar description]
     * @param  [type] $site [description]
     * @return [type]       [description]
     */
    public function liberar( ){

        $request = '<packet>
                        <webspace>
                            <set>
                                <filter>
                                    <name>'.$this->_site.'</name>
                                </filter>
                                <values>
                                    <gen_setup>
                                        <status>0</status>
                                    </gen_setup>
                                </values>
                            </set>
                        </webspace>
                    </packet>';
        
        $dados = simplexml_load_string(rtrim($this->request($request))); 

        $error = $this->validaRequest( $dados );

        if($error->erro){
            return $error;
        }

        $dados = $dados->webspace->set->result;

        $obj = new StdClass;
        $obj->id        =  $dados->id;
        $obj->tx_status = $dados->status;
        $obj->site      = $dados->{'filter-id'};

        return $dados;
    }
    /**
     * Description
     * @param type|string $site 
     * @return type
     */
    private function filterSite( $site = '' ){
        return rtrim(str_ireplace(array('https://www.','http://www.','www.','ww.','w.'), '', $site));
    }
    /**
     * Description
     * @param type $site 
     * @return type
     */
    private function validaSite( ){

        $obj = new StdClass;
        $obj->erro = false;
        if(empty($this->_site)){
            $obj->erro      = true;
            $obj->errocode  = 1;
            $obj->errotext  = 'Site Inválido';
            $obj->filtro    = $this->_site;
        }

        return $obj;
    }
    /**
     * Description
     * @param type $request 
     * @return type
     */
    private function validaRequest( $request = ''){
        $obj = new StdClass;
        $obj->erro = false;
        /**
         * Validação Vazio
         */
        if(empty($request)){
            $obj->erro      = true;
            $obj->errocode  = 123;
            $obj->errotext  = 'Resposta Vázia';
        }
        /**
         * Validação da Autenticação
         */
        if($request->system){
            $obj->erro      = true;
            $obj->errocode  = rtrim($request->system->errcode);
            $obj->errotext  = utf8_decode( $request->system->errtext );
            return $obj;
        }
        /**
         * Validação da Liberação, Add Webspace
         */
        if($request->webspace){
            if(rtrim($request->webspace->add->result->status) == 'error'){
                $obj->erro      = true;
                $obj->errocode  = rtrim($request->webspace->add->result->errcode);
                $obj->errotext  = utf8_decode( rtrim($request->webspace->add->result->errtext ));
            }

            return $obj;
        }
        /**
         * Validação Add Site
         */
        if($request->site->add){
            if(rtrim($request->site->add->result->status) == 'error'){
                $obj->erro      = true;
                $obj->errocode  = rtrim($request->site->add->result->errcode);
                $obj->errotext  = utf8_decode( rtrim($request->site->add->result->errtext ));
            }

            return $obj;
        }

        /**
         * Validação do Filtro
         */
        $status = $request->site->get->result->status;
        switch ($status) {
            case 'error':
                $obj->erro      = true;
                $obj->errocode  = rtrim($request->site->get->result->errcode);
                $obj->errotext  = utf8_decode( $request->site->get->result->errtext );
                $obj->filtro    = $request->site->get->result->{'filter-id'};
                break;
            default:
                # code...
                break;
        }

        return $obj;
    }  
}