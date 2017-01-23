<?php
/*
*   Descrição: API Cliente Plesk
*   Autor: Thiago R. Gham
*   Data: 02/01/2017
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
    private $_host;
    private $_port;
    private $_protocol;
    private $_login;
    private $_password;
    private $_secretKey;
    private $cd_status = array( 0    => 'STATUS_ACTIVE',
                                1    => 'STATUS_STATUS',
                                2    => 'STATUS_WITH_PARENT',
                                4    => 'STATUS_BACKUP_RESTORE',
                                16   => 'STATUS_ADMIN' ,
                                32   => 'STATUS_RESELLER',
                                64   => 'STATUS_CLIENT',
                                256  => 'STATUS_EXPIRED');

    private $tx_status = array( 'STATUS_ACTIVE'         => 'Ativo',
                                'STATUS_STATUS'         => 'Bloqueado',
                                'STATUS_WITH_PARENT'    => 'Bloqueado',
                                'STATUS_BACKUP_RESTORE' => 'Bloqueado por backup',
                                'STATUS_ADMIN'          => 'Bloqueado',
                                'STATUS_RESELLER'       => 'Bloqueado pela Revenda',
                                'STATUS_CLIENT'         => 'Bloqueado pelo Cliente',
                                'STATUS_EXPIRED'        => 'Bloqueado Expiração');      
    /**
     * [__construct description]
     * @param [type] $conf [description]
     */
    public function __construct($conf){
        
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
    public function setSecretKey($secretKey){
        $this->_secretKey = $secretKey;
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
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_getHeaders());
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }
    /**
     * [_getHeaders description]
     * @return [type] [description]
     */
    private function _getHeaders(){
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
     * [getInfo description]
     * @param  [type] $site [description]
     * @return [type]       [description]
     */
    public function getInfo($site){

        $request ='<packet>
                        <site>
                            <get>
                                <filter>
                                    <name>'.$site.'</name>
                                </filter>
                                <dataset>
                                    <gen_info/>
                                </dataset>
                            </get>
                        </site>
                    </packet>';

        $dados = simplexml_load_string(rtrim($this->request($request))); 
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
     * @param  [type] $site [description]
     * @return [type]       [description]
     */
    public function bloquear($site){
        
        $request = '<packet>
                        <webspace>
                            <set>
                                <filter>
                                    <name>'.$site.'</name>
                                </filter>
                                <values>
                                    <gen_setup>
                                        <status>16</status>
                                    </gen_setup>
                                </values>
                            </set>
                        </webspace>
                    </packet>';
        
        return rtrim($this->request($request));
    }
    /**
     * [liberar description]
     * @param  [type] $site [description]
     * @return [type]       [description]
     */
    public function liberar($site){
        
        $request = '<packet>
                        <webspace>
                            <set>
                                <filter>
                                    <name>'.$site.'</name>
                                </filter>
                                <values>
                                    <gen_setup>
                                        <status>0</status>
                                    </gen_setup>
                                </values>
                            </set>
                        </webspace>
                    </packet>';
        
        return rtrim($this->request($request));
    }    
}