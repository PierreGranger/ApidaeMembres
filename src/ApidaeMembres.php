<?php

/**
 * Documentation pour le fichier ApidaeMembres.php
 * 
 * 
 */

namespace PierreGranger;

/**
 * Documentation pour la classe ApidaeMembres
 * 
 * @author	Pierre Granger	<pierre@pierre-granger.fr>
 * 
 * 
 */
class ApidaeMembres extends ApidaeCore
{

	protected $projet_consultation_projetId = null;
	protected $projet_consultation_apiKey = null;

	private $servicesMU = [
		'GET' => ['utilisateur/get-by-id', 'utilisateur/get-by-mail', 'utilisateur/get-by-membre', 'utilisateur/get-all-utilisateurs', 'membre/get-by-id'],
		'POST' => ['membre/get-membres']
	];

	const EXCEPTION_NOT_IN_MEMBRES = 1;
	const EXCEPTION_NOT_FILLEUL = 2;
	const EXCEPTION_NO_PERMISSION = 3;

	public function __construct(array $params = null)
	{

		parent::__construct($params);

		$this->timeout = 30;

		if (isset($params['projet_consultation_projetId']) && preg_match('#^[0-9]+$#', $params['projet_consultation_projetId']))
			$this->projet_consultation_projetId = $params['projet_consultation_projetId'];
		else
			throw new \Exception('missing projet_consultation_projetId');

		if (isset($params['projet_consultation_apiKey']) && preg_match('#^[a-zA-Z0-9]{1,20}$#', $params['projet_consultation_apiKey']))
			$this->projet_consultation_apiKey = $params['projet_consultation_apiKey'];
		else
			throw new \Exception('missing projet_consultation_apiKey');
	}

	/**
	 * Récupère un Array des membres selon le $filter
	 * 
	 * @since	1.0
	 * 
	 * @return	array	Tableau associatif des membres
	 */
	public function getMembres(array $filter, $responseFields = null)
	{
		$query = [
			'projetId' => $this->projet_consultation_projetId,
			'apiKey' => $this->projet_consultation_apiKey,
			'filter' => $filter
		];
		if (isset($responseFields) && $responseFields != null)
			$query['responseFields'] = is_array($responseFields) ? json_encode($responseFields) : $responseFields;

		return $this->apidaeCurlMU('membre/get-membres', $query);
	}

	/**
	 * Récupère la liste des filleuls selon l'idParrain
	 * 
	 * @param int $idParrain	int	Identifiant du membre parrain
	 * @return array Tableau associatif des membres filleuls
	 */
	public function getFilleuls(int $idParrain, array $types = null)
	{
		$filter = ['idParrain' => $idParrain];
		if ($types == null || !is_array($types)) $types = ['Contributeur Généraliste'];
		if (is_array($types) && sizeof($types) > 0) $filter['types'] = $types;
		$responseFields = json_encode(["UTILISATEURS"]);
		return $this->getMembres($filter, $responseFields);
	}
	/**
	 * Récupération d'un utilisateur par son identifiant, via le service get-by-id de l'API Membres/utilisateurs d'Apidae
	 * @param int	$id_user
	 * @return array 
	 */
	public function getUserById(int $id_user)
	{
		if (!preg_match('#^[0-9]+$#', $id_user)) throw new \Exception(__LINE__ . " Invalid id_user for getUserById : " . $id_user);

		$query = [
			'projetId' => $this->projet_consultation_projetId,
			'apiKey' => $this->projet_consultation_apiKey
		];

		return $this->apidaeCurlMU('utilisateur/get-by-id', $query, $id_user);
	}
	public function getUtilisateur($var)
	{
		return $this->getUser($var);
	}

	public function getUser($var)
	{
		if (is_int($var)) return $this->getUserById($var);
		elseif ((strpos($var, '@')) !== false) return $this->getUserByMail($var);
		return false;
	}

	/**
	 * Récupération d'un utilisateur par son adresse mail, via le service get-by-mail de l'API Membres/utilisateurs d'Apidae
	 * @param string	$mail_user
	 * @return array 
	 */
	public function getUserByMail(string $mail_user)
	{
		if (false === filter_var($mail_user, FILTER_VALIDATE_EMAIL)) throw new \Exception(__LINE__ . " Invalid mail_user for getUserByMail : " . $mail_user);

		$params = [
			'projetId' => $this->projet_consultation_projetId,
			'apiKey' => $this->projet_consultation_apiKey
		];

		return $this->apidaeCurlMU('utilisateur/get-by-mail', $params, $mail_user);
	}

	/**
	 * Récupération de la liste des utilisateurs d'un membre par son identifiant, via le service get-by-membre de l'API Membres/utilisateurs d'Apidae
	 * @param int	$id_membre
	 * @return array 
	 */
	public function getUsersByMember(int $id_membre)
	{
		if (!preg_match('#^[0-9]+$#', $id_membre)) throw new \Exception(__LINE__ . ' Invalid id_membre for ' . __FUNCTION__ . ' : ' . $id_membre);

		$params = [
			'projetId' => $this->projet_consultation_projetId,
			'apiKey' => $this->projet_consultation_apiKey
		];

		return $this->apidaeCurlMU('utilisateur/get-by-membre', $params, $id_membre);
	}

	/**
	 *	Récupère un membre en fonction de son identifiant
	 * 	@param int $id_membre
	 * 	@return array
	 */
	public function getMembreById(int $id_membre, array $responseFields = null)
	{
		if (!preg_match('#^[0-9]+$#', $id_membre)) throw new \Exception(__LINE__ . ' Invalid id_membre for ' . __FUNCTION__ . ' : ' . $id_membre);

		$query = [
			'projetId' => $this->projet_consultation_projetId,
			'apiKey' => $this->projet_consultation_apiKey
		];
		if (isset($responseFields) && $responseFields != null && is_array($responseFields))
			$query['responseFields'] = json_encode($responseFields);

		return $this->apidaeCurlMU('membre/get-by-id', $query, $id_membre);
	}
	public function getMembre(int $id_membre, array $responseFields = null)
	{
		return $this->getMembreById($id_membre, $responseFields);
	}

	/**
	 * Cet appel ne liste pas les utilisateurs enregistrés ou parrainés.
	 */
	public function getAllUtilisateurs()
	{
		$this->timeout = 600;
		$query = [
			'projetId' => $this->projet_consultation_projetId,
			'apiKey' => $this->projet_consultation_apiKey
		];
		$ret = $this->apidaeCurlMU('utilisateur/get-all-utilisateurs', $query);
		$this->timeout = 15;
		return $ret;
	}

	/**
	 * Gestion des appels cURL aux API membres et utilisateurs d'Apidae
	 * 
	 * @param	string	$service	Service (chemin relatif)
	 * @param	array	$params	Liste de paramètres qui seront envoyées en cURL : en POST elles seront converties via json_encode, en GET elles seront converties via http_build_query.
	 */
	private function apidaeCurlMU(string $service, array $params = null, string $page = null)
	{
		$debug = $this->debug;

		$method = null;
		if (in_array($service, $this->servicesMU['GET'])) $method = 'GET';
		elseif (in_array($service, $this->servicesMU['POST'])) $method = 'POST';
		else throw new \Exception(__LINE__ . " Invalid function for apidaeCurl : " . $service);

		$url_base = '/api/v002/' . $service . '/';
		$url = $url_base;
		if ($page !== null && preg_match('#^[a-zA-Z0-9\@\.-]+$#', $page)) $url .= $page;

		/**
		 * 19/02/2021 Fun fact syntaxe responseFields :
		 * Méthode en GET :
		 * get-membres/?query={"projetId":X,"apiKey":"Y","filter":{"idProjet":Z},"responseFields":["PROJETS"]}
		 * $params["responseFields"] doit être DEJA json_encode, sinon il faut l'encoder ici
		 * Méthode en POST
		 * get-by-id/1157?projetId=X&apiKey=Y&responseFields=["PROJETS"]
		 * $params["responseFields"] ne doit PAS être json_encode
		 * Sinon on se retrouve avec responseFields="[\"PROJETS\"]"
		 */

		// On ne demande pas forcément du json, parce que la réponse peut être une 404 (donc format incorrect)
		//$request_params = Array('format' => 'json') ;

		$request_params = [];

		if ($method == 'GET') {
			if (isset($params['responseFields']) && is_array($params['responseFields']))
				$params['responseFields'] = json_encode($params['responseFields']);

			$url .= '?' . http_build_query($params);
		}

		if ($method == 'POST') {
			if (isset($params['responseFields']) && !is_array($params['responseFields'])) {
				$test = json_decode($params['responseFields'], true);
				if (json_last_error() == JSON_ERROR_NONE)
					$params['responseFields'] = $test;
			}

			// Force filter to {} instead of [] if empty
			if (isset($params['filter']) && is_array($params['filter']))
				$params['filter'] = (object)$params['filter'];

			$request_params['POST'] = 1;
			$request_params['POSTFIELDS'] = 'query=' . json_encode($params);
		}

		$result = $this->request($url, $request_params);

		if ($result['code'] == 204) return false;

		if ($result['code'] != 200) {
			$details = [];
			if ($this->debug) {
				$details['params'] = $params;
				$details['url'] = $url;
				$details['result'] = $result;
			}
			throw new ApidaeException(__CLASS__ . ':' . $service . ' : incorrect http_code ' . $result['code'], ApidaeException::INVALID_HTTPCODE, $details);
		}

		return json_decode($result['body']);
	}

	/**
	 * L'utilisateur a-t-il les droits demandés ?
	 * @param	$utilisateurApidae	Array	Utilisateur Apidae défini par l'authentification SSO
	 * @param	$droits	Array	Droits qu'on va rechercher sur cet utilisateur
	 * 							$droits['membres']	Array	Le membre de l'utilisateur est-il dans la liste $droits['membres'] ? (liste d'identifiants numériques)
	 * 							$droits['filleuls']	Integer	Le membre de l'utilisateur est-il filleur du membre $droits['filleuls'] ?
	 * 							$droits['permissions']	Array	L'utilisateur a-t-il les permissions $droits['permissions'] ? (liste de string)
	 * 
	 */
	public function droits($utilisateurApidae, $droits)
	{

		$return = true;

		foreach ($droits as $droit => $valeurs) {
			if ($droit == 'membres') {
				if (!in_array($utilisateurApidae['membre']['id'], $valeurs))
					return false;
			}

			if ($droit == 'filleuls') {
				$filleuls = $this->getFilleuls($valeurs);
				if (!in_array($utilisateurApidae['membre']['id'], $filleuls))
					return false;
			}

			if ($droit == 'permissions') {
				$usr = $this->getUserById($utilisateurApidae['id']);
				foreach ($valeurs as $p) {
					if (!in_array($p, $usr['permissions']))
						return false;
				}
			}
		}

		return $return;
	}
}
