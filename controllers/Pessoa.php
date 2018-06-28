<?php
namespace controllers{
	/*
	Classe pessoa
	*/
	class Pessoa{
		//Atributo para banco de dados
		private $PDO;

		
		//construtor para iniciar a conexão com o banco
		
		function __construct(){
			$this->PDO = new \PDO('mysql:host=localhost;dbname=api', 'root', ''); //Conexão
			$this->PDO->setAttribute( \PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION ); //habilitando erros do PDO
		}


		//listar pessoas + descricao
		public function lista(){
			global $app;
			$sth = $this->PDO->prepare("SELECT p.nome, p.email, t.descricao FROM pessoa as p INNER JOIN tipo_usuario as t ON p.id_tipo_usuario = t.id");
			$sth->execute();
			$result = $sth->fetchAll(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200);
			
		}
		 //listar pessoas + descricao especificando o id
		public function get($id){
			global $app;
			$sth = $this->PDO->prepare("SELECT p.nome, p.email, t.descricao FROM pessoa as p INNER JOIN tipo_usuario as t ON p.id_tipo_usuario = t.id WHERE p.id=:id");
			$sth ->bindValue(':id',$id);
			$sth->execute();
			$result = $sth->fetch(\PDO::FETCH_ASSOC);
			$app->render('default.php',["data"=>$result],200); 
		}


		//cadastrar pessoa
		public function nova(){
			global $app;
			$dados = json_decode($app->request->getBody(), true);
			$dados = (sizeof($dados)==0)? $_POST : $dados;
			//valida se existe o tipo no banco de dados
			$sth = $this->PDO->prepare("SELECT p.nome, t.descricao FROM pessoa as p, tipo_usuario as t WHERE :id_tipo_usuario = t.id ");
			$sth ->bindValue(':id_tipo_usuario',$dados['id_tipo_usuario']);
			$sth->execute();
			
			//adiciona a pessoa se existir o tipo no BD
			if ($sth->rowCount()>0) {
				unset($dados['descricao']);
				$keys = array_keys($dados); 
				$sth = $this->PDO->prepare("INSERT INTO pessoa (".implode(',', $keys).") VALUES (:".implode(',:', $keys).")");
				foreach ($dados as $key => $value) {
					$sth ->bindValue(':'.$key,$value);
				}
				$sth->execute();
				$app->render('default.php',["data"=>["Pessoa inserida com Sucesso"]],200); 
			}else{
				//adiciona o tipo pessoa e a pessoa ao mesmo tempo
				if (isset($dados['descricao'])) {
					$st= $this->PDO;
					// inicio da transaçao para validar a inserção das duas tabelas
					$st->beginTransaction();
					$sth= $st->prepare("INSERT INTO tipo_usuario (id,descricao) VALUES (:id,:descricao)");
					$sth->bindValue(':id',$dados['id_tipo_usuario']);
					$sth->bindValue(':descricao',$dados['descricao']);
					$sth->execute();
					unset($dados['descricao']);
					$keys=array_keys($dados);

					if (!$sth) {
						die("erro");
						$app->render('default.php',["data"=>[print_r("Erro")]],200);

					}
					
					$cpessoa= $st->prepare("INSERT INTO pessoa (".implode(',', $keys).") VALUES (:".implode(',:', $keys).")");
					foreach ($dados as $key => $value) {
						$cpessoa ->bindValue(':'.$key,$value); 
					}
					$cpessoa->execute();
					if (!$cpessoa) {
						die("erro");
						$app->render('default.php',["data"=>[print_r("Erro")]],200);
						$st->rollBack();
					}
					$st->commit();
					$app->render('default.php',["data"=>["Pessoa e decricao inseridos com Sucesso"]],200);			
				}
			}
		}

		public function editar($$id,$tabela){
			global $app;
			$dados = json_decode($app->request->getBody(), true);
			$dados = (sizeof($dados)==0)? $_POST : $dados;
			$sets = [];
			foreach ($dados as $key => $VALUES) {
				$sets[] = $key." = :".$key;
				$app->render('default.php',["data"=>print_r([$id])],200); 
			}
 
			$sth = $this->PDO->prepare("UPDATE :tabela SET nome= tailan WHERE id = :id");
			$sth ->bindValue(':id',$id);
			$sth ->bindValue(':tabela',$tabela);
			foreach ($dados as $key => $value) {
				$sth ->bindValue(':'.$key,$value);
			}
			
			$app->render('default.php',["data"=>['status'=>$sth->execute()==1]],200); 
		}
		//cadastrar pessoa ou tipo pessoa
		public function excluir($id,$tabela){
			global $app;
			$sth = $this->PDO->prepare("DELETE FROM $tabela WHERE id = :id");
			$sth ->bindValue(':id',$id);
			$sth ->bindValue(':tabela',$tabela);
			$app->render('default.php',["data"=>['status'=>$sth->execute()==1]],200); 
		}
	}
}
