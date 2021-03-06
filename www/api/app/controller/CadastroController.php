<?php
/**
 * Created by PhpStorm.
 * User: teoria
 * Date: 3/12/14
 * Time: 4:58 AM
 */

class CadastroController extends Controller {

    protected $msg;

    public function cadastraUsuario() {

        //"apelido":"","idade":"","sexo":"","email":"","senha":"","confirmacaoDeSenha":""
        $data = $this->request->post();

        $fachada = Fachada::getInstance();
        //$fachada = new Fachada();
        $vo = new UsuarioVO();
        $vo->setApelido($data['apelido']);
        $vo->setIdade($data['idade']);
        $vo->setSexo($data['sexo']);
        $vo->setEmail($data['email']);
        $vo->setSenha(MD5($data['senha']));
        $vo->setGcmid($data['gcmid']);
        $vo->setIdioma($data['idioma']);
        $vo->setDevice($data['device']);
        $vo->setPontuacao(0);

        $vo->setTotalPontosPossiveis($this->getTotalPontosPossiveis());

        $usuario = null;

        try {

            if (!$vo->isValid()) {
                throw new InvalidArgumentException;
            }


            $usuario = $fachada->selectOneByEmail($vo);


            if (!$usuario) {
                $fachada->insertUsuario($vo);
                $usuario = $fachada->selectOneByEmail($vo);

            } else {
                $usuario = null;
                $this->msg = "Usuário já cadastrado";
            }
        } catch (Exception $e) {
            $this->msg = "Dados Inválidos";

        }

        $retorno = $this->getResponse($usuario);


        echo $retorno;


    }

    private function getResponse($usuario) {

        $retorno = array(

            "status" => ($usuario != false),
            "mensagem" => $this->msg
        );
        return json_encode($retorno);
    }

    public function updateGcmid() {

        $data = $this->request->post();

        $fachada = Fachada::getInstance();

        $usuario = null;
        try {
            if (!($data && $data["usuario_id"] && $data['gcmid'])) {
                throw new InvalidArgumentException;
            }

            $id = (int)$fachada->decript($data["usuario_id"]);
            if( is_nan($id)){
                throw new InvalidArgumentException;
            }



            $vo = new UsuarioVO();
            $vo->setIdUsuario( $id );


            $usuario = $fachada->selectOneByID($vo);
            $usuario->setGcmid($data['gcmid']);

            if ($usuario) {
                $fachada->updateUser($usuario);


            } else {
                $usuario = null;
                $this->msg = "Usuário não encontrado";
            }
        } catch (Exception $e) {
            $this->msg = "Dados Inválidos";

        }

        $retorno = $this->getResponse($usuario);


        echo $retorno;


    }

    public function updateArena() {

        $data = $this->request->post();

        $fachada = Fachada::getInstance();

        $usuario = null;

        try {
            if (!($data && $data["usuario_id"] && $data['arena'])) {
                throw new InvalidArgumentException;
            }

            $id = (int)$fachada->decript($data["usuario_id"]);
            if( is_nan($id)){
                throw new InvalidArgumentException;
            }

            $vo = new UsuarioVO();
            $vo->setIdUsuario($id);

            $usuario = $fachada->selectOneByID($vo);
            $usuario->setArena($data['arena']);

            if ($usuario) {
                $fachada->updateUser($usuario);


            } else {
                $usuario = null;
                $this->msg = "Usuário não encontrado";
            }
        } catch (Exception $e) {
            $this->msg = "Dados Inválidos";

        }

        $retorno = $this->getResponse($usuario);


        echo $retorno;


    }

    private function getTotalPontosPossiveis() {

        $fachada = Fachada::getInstance();

        $datas = $fachada->getDatasJogo();


        $timeZone = new DateTimeZone('America/Chicago');

        $dateFinal = new DateTime();
        $dateFinal->setTimezone($timeZone);
        $dateFinal->setDate(2014, 7, 30);


        $dataAtual = new DateTime();
        $dataAtual->setTimezone($timeZone);


        $qtdDias = $this->dateDiff($dataAtual, $dateFinal);
        //echo $qtdDias;

        $datetemp = new DateTime();
        $totalPontosPossivel = 0;

        for ($i = 0; $i < $qtdDias; $i++) {
            $datetemp->modify("+ 1 days");
            $indice = $datetemp->format("d_m");
            $totalPontosPossivel += ($datas[$indice]) ? $datas[$indice] : 0;
        }

        return $totalPontosPossivel;
    }

    private function dateDiff(DateTime $startDate, DateTime $endDate) {

        $start = gmmktime(0, 0, 0, $startDate->format("m"), $startDate->format("d"), $startDate->format("Y"));
        $end = gmmktime(0, 0, 0, $endDate->format("m"), $endDate->format("d"), $endDate->format("Y"));

        return ($end - $start) / (60 * 60 * 24);
    }


}