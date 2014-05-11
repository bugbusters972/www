<?php

/**
 * requires history_api
 */
require_once( 'history_api.php' );
/**
 * requires email_api
 */
require_once( 'email_api.php' );
/**
 * requires bugnote_api
 */
require_once( 'bugnote_api.php' );
/**
 * requires file_api
 */
require_once( 'file_api.php' );
/**
 * requires string_api
 */
require_once( 'string_api.php' );
/**
 * requires sponsorship_api
 */
require_once( 'sponsorship_api.php' );
/**
 * requires twitter_api
 */
require_once( 'twitter_api.php' );
/**
 * requires tag_api
 */
require_once( 'tag_api.php' );
/**
 * requires relationship_api
 */
require_once( 'relationship_api.php' );
/**
 * requires bug_revision_api
 */
require_once( 'bug_revision_api.php' );

/**
 * Bug Data Structure Definition
 * @package UFM
 * @subpackage classes
 */
class BugData {
	protected $id;
	protected $project_id = null;
	protected $reporter_id = 0;
	protected $handler_id = 0;
	protected $duplicate_id = 0;
	#protected $motif_contact = NORMAL;
	protected $sexe = MINOR;
	protected $status = NEW_;
	protected $resolution = OPEN;
	protected $projection = 10;
	protected $category_id = 1;
	protected $date_submitted = '';
	protected $last_updated = '';
	protected $eta = 10;
	protected $os = '';
	protected $telephone = '';
	protected $nom = '';
	protected $nomepouse = '';
	protected $version = '';
	protected $fixed_in_version = '';
	protected $target_version = '';
	protected $build = '';
	protected $view_state = VS_PUBLIC;
	protected $summary = '';
	protected $sponsorship_total = 0;
	protected $sticky = 0;
	protected $due_date = 0;
	protected $attente= 0;
		protected $date_naissance= 0;
		protected $lieu_naissance= '';
		protected $pays_naissance= '';
		protected $nationalite= 0;
		protected $rue= '';
		protected $adrs_suite= '';
		protected $ville= '';
		#protected $region= '';
		protected $code_postal= 0;
		protected $pays= 0;
		protected $autre_hebergt= '';
		protected $email= '';
		protected $tel_domicile= 0;
		protected $tel_travail= 0;
		protected $orig_orient= 0;
		protected $connu_assoc= 0;
		protected $nom_logem= 0;
		protected $type_logem= 0;
		protected $complem_info= '';
		protected $matrim_ant= 0;
		protected $matrim= 0;
		protected $nb_enfant= 0;
		protected $nb_enf_charge= 0;
		protected $sxage_enf= '';
		protected $enf_pere= 0;
		protected $enf_recon= 0;
		protected $sit_prof= 0;
		protected $niv_scolr= 0;
		protected $emploi= '';
		protected $entreprise= '';
		protected $cddcdi= 0;
		protected $typ_contrat= 0;
		protected $secteur_activ= 0;
		protected $type_ent= 0;
		protected $csp= 0;
		protected $ressources= 0;
		protected $prestations= 0;
		protected $mere_victime= 0;
		protected $plaintes= 0;
		protected $nb_plaintes= 0;
		protected $nb_plaintesko= 0;
		protected $plaintes_gend= 0;
		protected $nb_plaintesgend= 0;
		protected $nb_plainteskogend= 0;
		protected $maincour= 0;
		protected $nb_maincour= 0;
		protected $suite_plaintes= '';
		protected $deja_vict= 0;
		protected $vict_enfce= 0;
		protected $contacte= 0;
		protected $note_general ='';

	# omitted:
	# var $bug_text_id
	protected $profile_id = 0;

	# extended info
	protected $description = '';
	protected $motif_nvvenue = 0;
	protected $date_nvvenue = 0;
	protected $rapporteur = 0;
	protected $type_contact = 20;

	# internal helper objects
	private $_stats = null;

	public $attachment_count = null;
	public $bugnotes_count = null;

	private $loading = false;

	/**
	 * return number of file attachment's linked to current bug
	 * @return int
	 */
	public function get_attachment_count() {
		if ( $this->attachment_count === null ) {
			$this->attachment_count = file_bug_attachment_count( $this->id );
			return $this->attachment_count;
		} else {
			return $this->attachment_count;
		}
	}

	/**
	 * return number of bugnotes's linked to current bug
	 * @return int
	 */
	public function get_bugnotes_count() {
		if ( $this->bugnotes_count === null ) {
			$this->bugnotes_count = self::bug_get_bugnote_count();
			return $this->bugnotes_count;
		} else {
			return $this->bugnotes_count;
		}
	}

	/**
	 * @private
	 */
	public function __set($name, $value) {
		switch ($name) {
			// integer types
			case 'id':
			case 'project_id':
			case 'reporter_id':
			case 'handler_id':
			case 'duplicate_id':
			#case 'motif_contact':
			case 'sexe':
			case 'type_contact':
			case 'status':
			case 'resolution':
			case 'projection':
			case 'category_id':
				$value = (int)$value;
				break;
			case 'target_version':
				if ( !$this->loading ) {
					# Only set target_version if user has access to do so
					if( !access_has_project_level( config_get( 'roadmap_update_threshold' ) ) ) {
						trigger_error( ERROR_ACCESS_DENIED, ERROR );
					}
				}
				break;
			case 'due_date':
				if ( !is_numeric( $value ) ) {
					$value = strtotime($value);
				}
				break;
		}
		$this->$name = $value;
	}

	/**
	 * @private
	 */
	public function __get($name) {
		if( $this->is_extended_field($name) )
			$this->fetch_extended_info();
		return $this->{$name};
	}

	/**
	 * @private
	 */
	public function __isset($name) {
		return isset( $this->{$name} );
	}

	/**
	 * fast-load database row into bugobject
	 * @param array $p_row
	 */
	public function loadrow( $p_row ) {
		$this->loading = true;

		foreach( $p_row as $var => $val ) {
			$this->__set( $var, $p_row[$var] );
		}
		$this->loading = false;
	}

	/**
	 * Retrieves extended information for bug (e.g. bug description)
	 * @return null
	 */
	private function fetch_extended_info() {
		if ( $this->description == '' ) {
			$t_text = bug_text_cache_row($this->id);

			$this->description = $t_text['description'];
			$this->motif_nvvenue = $t_text['motif_nvvenue'];
			$this->date_nvvenue = $t_text['date_nvvenue'];
			$this->rapporteur = $t_text['rapporteur'];
			$this->type_contact = $t_text['type_contact'];
		}
	}

	/**
	 * Returns if the field is an extended field which needs fetch_extended_info()
	 * @return boolean
	 */
	private function is_extended_field( $p_field_name ) {
		switch( $p_field_name ) {
			case 'description':
			case 'motif_nvvenue':
			case 'date_nvvenue':
			case 'rapporteur':
			case 'type_contact':
			case 'dossier':
				return true;
			default:
				return false;
		}
	}

	/**
	 * Returns the number of bugnotes for the given bug_id
	 * @return int number of bugnotes
 	 * @access private
	 * @uses database_api.php
 	 */
	private function bug_get_bugnote_count() {
		if( !access_has_project_level( config_get( 'private_bugnote_threshold' ), $this->project_id ) ) {
			$t_restriction = 'AND view_state=' . VS_PUBLIC;
		} else {
			$t_restriction = '';
		}

		$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );
		$query = "SELECT COUNT(*)
					  FROM $t_bugnote_table
					  WHERE bug_id =" . db_param() . " $t_restriction";
		$result = db_query_bound( $query, Array( $this->bug_id ) );

		return db_result( $result );
	}

	/**
	 * validate current bug object for database insert/update
	 * triggers error on failure
	 * @param bool $p_update_extended
	 */
	function validate( $p_update_extended =  true) {
		# nom cannot be blank
		if( is_blank( $this->nom ) ) {
			error_parameters( lang_get( 'nom' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}
		if( is_blank( $this->prenom ) ) {
			error_parameters( lang_get( 'prenom' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}
		if( is_blank( $this->telephone ) ) {
			error_parameters( lang_get( 'telephone' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}


		# Make sure a category is set
		if( 0 == $this->category_id && !config_get( 'allow_no_category' ) ) {
			error_parameters( lang_get( 'category' ) );
			trigger_error( ERROR_EMPTY_FIELD, ERROR );
		}

		if( !is_blank( $this->duplicate_id ) && ( $this->duplicate_id != 0 ) && ( $this->id == $this->duplicate_id ) ) {
			trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );
			# never returns
		}
	}

	/**
	 * Insert a new bug into the database
	 * @return int integer representing the bug id that was created
	 * @access public
	 * @uses database_api.php
	 * @uses lang_api.php
	 */
	function create() {
		self::validate( true );

		# check due_date format
		if( is_blank( $this->due_date ) ) {
			$this_due_date = date_get_null();
		}

		$t_bug_table = db_get_table( 'mantis_bug_table' );
		$t_bug_text_table = db_get_table( 'mantis_bug_text_table' );
		$t_category_table = db_get_table( 'mantis_category_table' );



		# check to see if we want to assign this right off
		$t_starting_status  = config_get( 'bug_submit_status' );
		$t_original_status = $this->status;

		# if not assigned, check if it should auto-assigned.
		if( 0 == $this->handler_id ) {
			# if a default user is associated with the category and we know at this point
			# that that the bug was not assigned to somebody, then assign it automatically.
			$query = "SELECT user_id
						  FROM $t_category_table
						  WHERE id=" . db_param();
			$result = db_query_bound( $query, array( $this->category_id ) );

			if( db_num_rows( $result ) > 0 ) {
				$this->handler_id = db_result( $result );
			}
		}

		# Check if bug was pre-assigned or auto-assigned.
		if( ( $this->handler_id != 0 ) && ( $this->status == $t_starting_status ) && ( ON == config_get( 'auto_set_status_to_assigned' ) ) ) {
			$t_status = config_get( 'bug_assigned_status' );
		} else {
			$t_status = $this->status;
		}

		# Insert the rest of the data
		$query = "INSERT INTO $t_bug_table
					    ( project_id,reporter_id, handler_id,duplicate_id,
					      sexe, status,
					      resolution,projection, category_id,date_submitted,
					      last_updated,eta, 
					      prenom, telephone,nom,nomepouse, version,build,
					      profile_id, summary, view_state, sponsorship_total, sticky, fixed_in_version,
					      target_version, due_date, attente, date_naissance, lieu_naissance,pays_naissance, nationalite, rue, adrs_suite, ville,  code_postal, pays, autre_hebergt, email, tel_domicile, tel_travail, orig_orient, connu_assoc, nom_logem, type_logem, complem_info, matrim_ant, matrim, nb_enfant, nb_enf_charge, sxage_enf, enf_pere, enf_recon, sit_prof, niv_scolr, emploi, entreprise, cddcdi, typ_contrat, secteur_activ, type_ent, csp, ressources, prestations, mere_victime, plaintes, nb_plaintes, nb_plaintesko, plaintes_gend, nb_plaintesgend, nb_plainteskogend, maincour, nb_maincour, suite_plaintes, deja_vict, vict_enfce, contacte, note_general
					    )
					  VALUES
					    ( " . db_param() .  ',' . db_param() . ","
					      . db_param() . ',' . db_param() . ',' . db_param() . ",
					      " . db_param() . ',' . db_param() . ',' . db_param() . ",
					      " . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ",
					      " . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ",
					      " . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() . ",
					      " . db_param() . ',' . db_param() . ',' . db_param() . ',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() .',' . db_param() . ')';

		db_query_bound( $query, Array( $this->project_id, $this->reporter_id, $this->handler_id, $this->duplicate_id,  $this->sexe, $t_status, $this->resolution, $this->projection, $this->category_id, db_now(), db_now(), $this->eta, $this->prenom, $this->telephone, $this->nom, $this->nomepouse, $this->version, $this->build, $this->profile_id, $this->summary, $this->view_state, $this->sponsorship_total, $this->sticky, $this->fixed_in_version, $this->target_version, $this->due_date, $this->attente , $this->date_naissance , $this->lieu_naissance, $this->pays_naissance , $this->nationalite  , $this->rue , $this->adrs_suite , $this->ville ,  $this->code_postal , $this->pays , $this->autre_hebergt , $this->email , $this->tel_domicile , $this->tel_travail , $this->orig_orient , $this->connu_assoc , $this->nom_logem , $this->type_logem , $this->complem_info , $this->matrim_ant , $this->matrim , $this->nb_enfant , $this->nb_enf_charge , $this->sxage_enf , $this->enf_pere , $this->enf_recon , $this->sit_prof , $this->niv_scolr , $this->emploi , $this->entreprise , $this->cddcdi , $this->typ_contrat , $this->secteur_activ , $this->type_ent , $this->csp , $this->ressources , $this->prestations , $this->mere_victime , $this->plaintes , $this->nb_plaintes , $this->nb_plaintesko , $this->plaintes_gend , $this->nb_plaintesgend , $this->nb_plainteskogend , $this->maincour , $this->nb_maincour , $this->suite_plaintes , $this->deja_vict , $this->vict_enfce , $this->contacte , $this->note_general ) );

		$this->id = db_insert_id( $t_bug_table );

		# Insert text information
		$query = "INSERT INTO $t_bug_text_table
					    ( description, motif_nvvenue, date_nvvenue, rapporteur, type_contact,dossier )
					  VALUES
					    ( " . db_param() . ',' . db_param() . ','. db_param() . ','. db_param() . ','. db_param() . ',' . db_param() . ')';
		db_query_bound( $query, Array( $this->description, $this->motif_nvvenue, $this->date_nvvenue, $this->rapporteur, $this->type_contact, $this->id ) );

		# Get the id of the text information we just inserted
		# NOTE: this is guarranteed to be the correct one.
		# The value LAST_INSERT_ID is stored on a per connection basis.

		#$t_text_id = db_insert_id( $t_bug_text_table );
		
		# log new bug
		#history_log_event_special( $this->id, NEW_BUG );

		# log changes, if any (compare happens in history_log_event_direct)
		#history_log_event_direct( $this->id, 'status', $t_original_status, $t_status );
		#history_log_event_direct( $this->id, 'handler_id', 0, $this->handler_id );

		return $this->id;
	}

	/**
	 * Update a bug from the given data structure
	 *  If the third parameter is true, also update the longer strings table
	 * @param bool p_update_extended
	 * @param bool p_bypass_email Default false, set to true to avoid generating emails (if sending elsewhere)
	 * @return bool (always true)
	 * @access public
	 */
	function update( $p_update_extended = false, $p_bypass_mail = false ) {
	
		self::validate( $p_update_extended );

		$c_bug_id = $this->id;

		if( is_blank( $this->due_date ) ) {
			$this->due_date = date_get_null();
		}

		$t_old_data = bug_get( $this->id, true );

		$t_bug_table = db_get_table( 'mantis_bug_table' );

		# Update all fields
		# Ignore date_submitted and last_updated since they are pulled out
		#  as unix timestamps which could confuse the history log and they
		#  shouldn't get updated like this anyway.  If you really need to change
		#  them use bug_set_field()
		$query = "UPDATE $t_bug_table
					SET project_id=" . db_param() . ', reporter_id=' . db_param() . ",
						handler_id=" . db_param() . ', duplicate_id=' . db_param() . ",
						sexe=" . db_param() . ",
						 status=" . db_param() . ",
						resolution=" . db_param() . ', projection=' . db_param() . ",
						category_id=" . db_param() . ', eta=' . db_param() . ",
						prenom=" . db_param() . ', telephone=' . db_param() . ",
						nom=" . db_param() . ',nomepouse=' . db_param() .", version=" . db_param() . ',
						build=' . db_param() . ", fixed_in_version=" . db_param() . ",attente=" . db_param() . ",
						date_naissance=" . db_param() . ",
						lieu_naissance=" . db_param() . ",
						pays_naissance=" . db_param() . ",
						nationalite=" . db_param() . ",
						rue=" . db_param() . ",
						adrs_suite=" . db_param() . ",
						ville=" . db_param() . ",
			
						code_postal=" . db_param() . ",
						pays=" . db_param() . ",
						autre_hebergt=" . db_param() . ",
						email=" . db_param() . ",
						tel_domicile=" . db_param() . ",
						tel_travail=" . db_param() . ",
						orig_orient=" . db_param() . ",
						connu_assoc=" . db_param() . ",
						nom_logem=" . db_param() . ",
						type_logem=" . db_param() . ",
						complem_info=" . db_param() . ",
						matrim_ant=" . db_param() . ",
						matrim=" . db_param() . ",
						nb_enfant=" . db_param() . ",
						nb_enf_charge=" . db_param() . ",
						sxage_enf=" . db_param() . ",
						enf_pere=" . db_param() . ",
						enf_recon=" . db_param() . ",
						sit_prof=" . db_param() . ",
						niv_scolr=" . db_param() . ",
						emploi=" . db_param() . ",
						entreprise=" . db_param() . ",
						cddcdi=" . db_param() . ",
						typ_contrat=" . db_param() . ",
						secteur_activ=" . db_param() . ",
						type_ent=" . db_param() . ",
						csp=" . db_param() . ",
						ressources=" . db_param() . ",
						prestations=" . db_param() . ",
						mere_victime=" . db_param() . ",
						plaintes=" . db_param() . ",
						nb_plaintes=" . db_param() . ",
						nb_plaintesko=" . db_param() . ",
						plaintes_gend=" . db_param() . ",
						nb_plaintesgend=" . db_param() . ",
						nb_plainteskogend=" . db_param() . ",
						maincour=" . db_param() . ",
						nb_maincour=" . db_param() . ",
						suite_plaintes=" . db_param() . ",
						vict_enfce=" . db_param() . ",
						deja_vict=" . db_param() . ",
						contacte=" . db_param() . ",
						note_general=" . db_param() . ",";

		$t_fields = Array(
			$this->project_id, $this->reporter_id,
			$this->handler_id, $this->duplicate_id,
			$this->sexe,
			$this->status,
			$this->resolution, $this->projection,
			$this->category_id, $this->eta,
			$this->prenom, $this->telephone,
			$this->nom ,$this->nomepouse, $this->version,
			$this->build, $this->fixed_in_version,
			$this->attente,
			$this->date_naissance,
			$this->lieu_naissance,
			$this->pays_naissance,
			$this->nationalite,
			$this->rue,
			$this->adrs_suite,
			$this->ville,
			$this->code_postal,
			$this->pays,
			$this->autre_hebergt,
			$this->email,
			$this->tel_domicile,
			$this->tel_travail,
			$this->orig_orient,
			$this->connu_assoc,
			$this->nom_logem,
			$this->type_logem,
			$this->complem_info,
			$this->matrim_ant,
			$this->matrim,
			$this->nb_enfant,
			$this->nb_enf_charge,
			$this->sxage_enf,
			$this->enf_pere,
			$this->enf_recon,
			$this->sit_prof,
			$this->niv_scolr,
			$this->emploi,
			$this->entreprise,
			$this->cddcdi,
			$this->typ_contrat,
			$this->secteur_activ,
			$this->type_ent,
			$this->csp,
			$this->ressources,
			$this->prestations,
			$this->mere_victime,
			$this->plaintes,
			$this->nb_plaintes,
			$this->nb_plaintesko,
			$this->plaintes_gend,
			$this->nb_plaintesgend,
			$this->nb_plainteskogend,
			$this->maincour,
			$this->nb_maincour,
			$this->suite_plaintes,
			$this->deja_vict,
			$this->vict_enfce,
			$this->contacte,
			$this->note_general
		);
		/*$t_roadmap_updated = false;
		if( access_has_project_level( config_get( 'roadmap_update_threshold' ) ) ) {
			$query .= "
						target_version=" . db_param() . ",";
			$t_fields[] = $this->target_version;
			$t_roadmap_updated = true;
		}*/

		$query .= "
						view_state=" . db_param() . ",
						summary=" . db_param() . ",
						sponsorship_total=" . db_param() . ",
						sticky=" . db_param() . ",
						due_date=" . db_param() . "
					WHERE id=". db_param();
					
		$t_fields[] = $this->view_state;
		$t_fields[] = $this->summary;
		$t_fields[] = $this->sponsorship_total;
		$t_fields[] = (bool)$this->sticky;
		$t_fields[] = $this->due_date;
		$t_fields[] = $this->id;

		db_query_bound( $query, $t_fields );

		bug_clear_cache( $this->id );

		# log changes
		/*history_log_event_direct( $c_bug_id, 'project_id', $t_old_data->project_id, $this->project_id );
		history_log_event_direct( $c_bug_id, 'reporter_id', $t_old_data->reporter_id, $this->reporter_id );
		history_log_event_direct( $c_bug_id, 'handler_id', $t_old_data->handler_id, $this->handler_id );
		history_log_event_direct( $c_bug_id, 'motif_contact', $t_old_data->motif_contact, $this->motif_contact );
		history_log_event_direct( $c_bug_id, 'sexe', $t_old_data->sexe, $this->sexe );
		history_log_event_direct( $c_bug_id, 'type_contact', $t_old_data->type_contact, $this->type_contact );
		history_log_event_direct( $c_bug_id, 'status', $t_old_data->status, $this->status );
		history_log_event_direct( $c_bug_id, 'resolution', $t_old_data->resolution, $this->resolution );
		history_log_event_direct( $c_bug_id, 'projection', $t_old_data->projection, $this->projection );
		history_log_event_direct( $c_bug_id, 'category', category_full_name( $t_old_data->category_id, false ), category_full_name( $this->category_id, false ) );
		history_log_event_direct( $c_bug_id, 'eta', $t_old_data->eta, $this->eta );
		history_log_event_direct( $c_bug_id, 'prenom', $t_old_data->prenom, $this->prenom );
		history_log_event_direct( $c_bug_id, 'telephone', $t_old_data->telephone, $this->telephone );
		history_log_event_direct( $c_bug_id, 'nom', $t_old_data->nom, $this->nom );
		history_log_event_direct( $c_bug_id, 'nomepouse', $t_old_data->nomepouse, $this->nomepouse );
		history_log_event_direct( $c_bug_id, 'version', $t_old_data->version, $this->version );
		history_log_event_direct( $c_bug_id, 'build', $t_old_data->build, $this->build );
		history_log_event_direct( $c_bug_id, 'fixed_in_version', $t_old_data->fixed_in_version, $this->fixed_in_version );
		if( $t_roadmap_updated ) {
			history_log_event_direct( $c_bug_id, 'target_version', $t_old_data->target_version, $this->target_version );
		}
		history_log_event_direct( $c_bug_id, 'view_state', $t_old_data->view_state, $this->view_state );
		history_log_event_direct( $c_bug_id, 'summary', $t_old_data->summary, $this->summary );
		history_log_event_direct( $c_bug_id, 'sponsorship_total', $t_old_data->sponsorship_total, $this->sponsorship_total );
		history_log_event_direct( $c_bug_id, 'sticky', $t_old_data->sticky, $this->sticky );

		history_log_event_direct( $c_bug_id, 'due_date', ( $t_old_data->due_date != date_get_null() ) ? $t_old_data->due_date : null, ( $this->due_date != date_get_null() ) ? $this->due_date : null );*/

		# Update extended info if requested
		if( $p_update_extended ) {
			$t_bug_text_table = db_get_table( 'mantis_bug_text_table' );

			$t_bug_text_id = bug_get_field( $c_bug_id, 'bug_text_id' );

			$query = "INSERT INTO $t_bug_text_table
							( description,
							type_contact,
								motif_nvvenue,
								date_nvvenue,
								rapporteur,
								dossier) VALUES
								(". db_param() .','
								. db_param() .','
								. db_param() .','
								. db_param() .','
								. db_param() .','
								. db_param() .")";
			db_query_bound( $query, Array( $this->description, $this->type_contact, $this->motif_nvvenue, $this->date_nvvenue, $this->rapporteur, $this->dossier ) );

			bug_text_clear_cache( $c_bug_id );

			$t_current_user = auth_get_current_user_id();

			/*if( $t_old_data->description != $this->description ) {
				if ( bug_revision_count( $c_bug_id, REV_DESCRIPTION ) < 1 ) {
					$t_revision_id = bug_revision_add( $c_bug_id, $t_old_data->reporter_id, REV_DESCRIPTION, $t_old_data->description, 0, $t_old_data->date_submitted );
				}
				$t_revision_id = bug_revision_add( $c_bug_id, $t_current_user, REV_DESCRIPTION, $this->description );
				history_log_event_special( $c_bug_id, DESCRIPTION_UPDATED, $t_revision_id );
			}

			if( $t_old_data->motif_nvvenue != $this->motif_nvvenue ) {
				if ( bug_revision_count( $c_bug_id, REV_DATE_NVVENUE ) < 1 ) {
					$t_revision_id = bug_revision_add( $c_bug_id, $t_old_data->reporter_id, REV_motif_nvvenue, $t_old_data->motif_nvvenue, 0, $t_old_data->date_submitted );
				}
				$t_revision_id = bug_revision_add( $c_bug_id, $t_current_user, REV_motif_nvvenue, $this->motif_nvvenue );
				history_log_event_special( $c_bug_id, STEP_TO_REPRODUCE_UPDATED, $t_revision_id );
			}

			if( $t_old_data->date_nvvenue != $this->date_nvvenue ) {
				if ( bug_revision_count( $c_bug_id, REV_DATE_NVVENUE ) < 1 ) {
					$t_revision_id = bug_revision_add( $c_bug_id, $t_old_data->reporter_id, REV_DATE_NVVENUE, $t_old_data->date_nvvenue, 0, $t_old_data->date_submitted );
				}
				$t_revision_id = bug_revision_add( $c_bug_id, $t_current_user, REV_DATE_NVVENUE, $this->date_nvvenue );
				history_log_event_special( $c_bug_id, DATE_NVVENUE_UPDATED, $t_revision_id );
			}*/
		}

		# Update the last update date
		bug_update_date( $c_bug_id );

		# allow bypass if user is sending mail separately
		if( false == $p_bypass_mail ) {
			# bug assigned
			if( $t_old_data->handler_id != $this->handler_id ) {
				#email_generic( $c_bug_id, 'owner', 'email_notification_title_for_action_bug_assigned' );
				return true;
			}

			# status changed
			if( $t_old_data->status != $this->status ) {
				$t_status = MantisEnum::getLabel( config_get( 'status_enum_string' ), $this->status );
				$t_status = str_replace( ' ', '_', $t_status );
				#email_generic( $c_bug_id, $t_status, 'email_notification_title_for_status_bug_' . $t_status );
				return true;
			}

			# @todo handle motif_contact change if it requires special handling
			# generic update notification
			#email_generic( $c_bug_id, 'updated', 'email_notification_title_for_action_bug_updated' );
		}

		return true;
	}
}

$g_cache_bug = array();
$g_cache_bug_text = array();

/**
 * Cache a database result-set containing full contents of bug_table row.
 * @param array p_bug_database_result database row containing all columns from mantis_bug_table
 * @param array p_stats (optional) array representing bugnote stats
 * @return array returns an array representing the bug row if bug exists
 * @access public
 */
function bug_cache_database_result( $p_bug_database_result, $p_stats = null ) {
	global $g_cache_bug;

	if( !is_array( $p_bug_database_result ) || isset( $g_cache_bug[(int) $p_bug_database_result['id']] ) ) {
		return $g_cache_bug[(int) $p_bug_database_result['id']];
	}

	return bug_add_to_cache( $p_bug_database_result, $p_stats );
}

/**
 * Cache a bug row if necessary and return the cached copy
 * @param array p_bug_id id of bug to cache from mantis_bug_table
 * @param array p_trigger_errors set to true to trigger an error if the bug does not exist.
 * @return bool|array returns an array representing the bug row if bug exists or false if bug does not exist
 * @access public
 * @uses database_api.php
 */
function bug_cache_row( $p_bug_id, $p_trigger_errors = true ) {
	global $g_cache_bug;

	if( isset( $g_cache_bug[$p_bug_id] ) ) {
		return $g_cache_bug[$p_bug_id];
	}

	$c_bug_id = (int) $p_bug_id;
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$query = "SELECT *
				  FROM $t_bug_table
				  WHERE id=" . db_param();
	$result = db_query_bound( $query, Array( $c_bug_id ) );

	if( 0 == db_num_rows( $result ) ) {
		$g_cache_bug[$c_bug_id] = false;

		if( $p_trigger_errors ) {
			error_parameters( $p_bug_id );
			trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
		} else {
			return false;
		}
	}

	$row = db_fetch_array( $result );

	return bug_add_to_cache( $row );
}

/**
 * Cache a set of bugs
 * @param array p_bug_id_array integer array representing bug ids to cache
 * @return null
 * @access public
 * @uses database_api.php
 */
function bug_cache_array_rows( $p_bug_id_array ) {
	global $g_cache_bug;
	$c_bug_id_array = array();

	foreach( $p_bug_id_array as $t_bug_id ) {
		if( !isset( $g_cache_bug[(int) $t_bug_id] ) ) {
			$c_bug_id_array[] = (int) $t_bug_id;
		}
	}

	if( empty( $c_bug_id_array ) ) {
		return;
	}

	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$query = "SELECT *
				  FROM $t_bug_table
				  WHERE id IN (" . implode( ',', $c_bug_id_array ) . ')';
	$result = db_query_bound( $query );

	while( $row = db_fetch_array( $result ) ) {
		bug_add_to_cache( $row );
	}
	return;
}

/**
 * Inject a bug into the bug cache
 * @param array p_bug_row bug row to cache
 * @param array p_stats bugnote stats to cache
 * @return null
 * @access private
 */
function bug_add_to_cache( $p_bug_row, $p_stats = null ) {
	global $g_cache_bug;

	$g_cache_bug[(int) $p_bug_row['id']] = $p_bug_row;

	if( !is_null( $p_stats ) ) {
		$g_cache_bug[(int) $p_bug_row['id']]['_stats'] = $p_stats;
	}

	return $g_cache_bug[(int) $p_bug_row['id']];
}

/**
 * Clear a bug from the cache or all bugs if no bug id specified.
 * @param int bug id to clear (optional)
 * @return null
 * @access public
 */
function bug_clear_cache( $p_bug_id = null ) {
	global $g_cache_bug;

	if( null === $p_bug_id ) {
		$g_cache_bug = array();
	} else {
		unset( $g_cache_bug[(int) $p_bug_id] );
	}

	return true;
}

/**
 * Cache a bug text row if necessary and return the cached copy
 * @param int p_bug_id integer bug id to retrieve text for
 * @param bool p_trigger_errors If the second parameter is true (default), trigger an error if bug text not found.
 * @return bool|array returns false if not bug text found or array of bug text
 * @access public
 * @uses database_api.php
 */
function bug_text_cache_row( $p_bug_id, $p_trigger_errors = true ) {
	global $g_cache_bug_text;

	$c_bug_id = (int) $p_bug_id;
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_bug_text_table = db_get_table( 'mantis_bug_text_table' );

	if( isset( $g_cache_bug_text[$c_bug_id] ) ) {
		return $g_cache_bug_text[$c_bug_id];
	}
	


	$query = "SELECT *
				  FROM $t_bug_text_table 
				  WHERE dossier=" . db_param();
				  
	$result = db_query_bound( $query, Array( $c_bug_id ) );

	if( 0 == db_num_rows( $result ) ) {
		$g_cache_bug_text[$c_bug_id] = false;

		if( $p_trigger_errors ) {
			error_parameters( $p_bug_id );
			trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
		} else {
			return false;
		}
	}
	
	$row = db_fetch_array( $result );
	
	$g_cache_bug_text[$c_bug_id] = $row;

	return $row;
	

}

/**
 * Clear a bug's bug text from the cache or all bug text if no bug id specified.
 * @param int bug id to clear (optional)
 * @return null
 * @access public
 */
function bug_text_clear_cache( $p_bug_id = null ) {
	global $g_cache_bug_text;

	if( null === $p_bug_id ) {
		$g_cache_bug_text = array();
	} else {
		unset( $g_cache_bug_text[(int) $p_bug_id] );
	}

	return true;
}

/**
 * Check if a bug exists
 * @param int p_bug_id integer representing bug id
 * @return bool true if bug exists, false otherwise
 * @access public
 */
function bug_exists( $p_bug_id ) {
	if( false == bug_cache_row( $p_bug_id, false ) ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Check if a bug exists. If it doesn't then trigger an error
 * @param int p_bug_id integer representing bug id
 * @return null
 * @access public
 */
function bug_ensure_exists( $p_bug_id ) {
	if( !bug_exists( $p_bug_id ) ) {
		error_parameters( $p_bug_id );
		trigger_error( ERROR_BUG_NOT_FOUND, ERROR );
	}
}

/**
 * check if the given user is the reporter of the bug
 * @param int p_bug_id integer representing bug id
 * @param int p_user_id integer reprenting a user id
 * @return bool return true if the user is the reporter, false otherwise
 * @access public
 */
function bug_is_user_reporter( $p_bug_id, $p_user_id ) {
	if( bug_get_field( $p_bug_id, 'reporter_id' ) == $p_user_id ) {
		return true;
	} else {
		return false;
	}
}

/**
 * check if the given user is the handler of the bug
 * @param int p_bug_id integer representing bug id
 * @param int p_user_id integer reprenting a user id
 * @return bool return true if the user is the handler, false otherwise
 * @access public
 */
function bug_is_user_handler( $p_bug_id, $p_user_id ) {
	if( bug_get_field( $p_bug_id, 'handler_id' ) == $p_user_id ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Check if the bug is readonly and shouldn't be modified
 * For a bug to be readonly the status has to be >= bug_readonly_status_threshold and
 * current user access level < update_readonly_bug_threshold.
 * @param int p_bug_id integer representing bug id
 * @return bool
 * @access public
 * @uses access_api.php
 * @uses config_api.php
 */
function bug_is_readonly( $p_bug_id ) {
	$t_status = bug_get_field( $p_bug_id, 'status' );
	if( $t_status < config_get( 'bug_readonly_status_threshold' ) ) {
		return false;
	}

	if( access_has_bug_level( config_get( 'update_readonly_bug_threshold' ), $p_bug_id ) ) {
		return false;
	}

	return true;
}

/**
 * Check if a given bug is resolved
 * @param int p_bug_id integer representing bug id
 * @return bool true if bug is resolved, false otherwise
 * @access public
 * @uses config_api.php
 */
function bug_is_resolved( $p_bug_id ) {
	$t_bug = bug_get( $p_bug_id );
	return( $t_bug->status >= config_get( 'bug_resolved_status_threshold', null, null, $t_bug->project_id ) );
}

/**
 * Check if a given bug is closed
 * @param int p_bug_id integer representing bug id
 * @return bool true if bug is closed, false otherwise
 * @access public
 * @uses config_api.php
 */
function bug_is_closed( $p_bug_id ) {
	$t_bug = bug_get( $p_bug_id );
	return( $t_bug->status >= config_get( 'bug_closed_status_threshold', null, null, $t_bug->project_id ) );
}

/**
 * Check if a given bug is overdue
 * @param int p_bug_id integer representing bug id
 * @return bool true if bug is overdue, false otherwise
 * @access public
 * @uses database_api.php
 */
function bug_is_overdue( $p_bug_id ) {
	$t_due_date = bug_get_field( $p_bug_id, 'due_date' );
	if( !date_is_null( $t_due_date ) ) {
		$t_now = db_now();
		if( $t_now > $t_due_date ) {
			if( !bug_is_resolved( $p_bug_id ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Validate workflow state to see if bug can be moved to requested state
 * @param int p_bug_status current bug status
 * @param int p_wanted_status new bug status
 * @return bool
 * @access public
 * @uses config_api.php
 * @uses utility_api.php
 */
function bug_check_workflow( $p_bug_status, $p_wanted_status ) {
	$t_status_enum_workflow = config_get( 'status_enum_workflow' );

	if( count( $t_status_enum_workflow ) < 1 ) {

		# workflow not defined, use default enum
		return true;
	}

	if ( $p_bug_status == $p_wanted_status ) {
		# no change in state, allow the transition
		return true;
	}

	# There should always be a possible next status, if not defined, then allow all.
	if ( !isset( $t_status_enum_workflow[$p_bug_status] ) ) {
		return true;
	}

	# workflow defined - find allowed states
	$t_allowed_states = $t_status_enum_workflow[$p_bug_status];

	return MantisEnum::hasValue( $t_allowed_states, $p_wanted_status );
}

/**
 * Copy a bug from one project to another. Also make copies of issue notes, attachments, history,
 * email notifications etc.
 * @todo Not managed FTP file upload
 * @param array p_bug_id integer representing bug id
 * @param int p_target_project_id
 * @param bool p_copy_custom_fields
 * @param bool p_copy_relationships
 * @param bool p_copy_history
 * @param bool p_copy_attachments
 * @param bool p_copy_bugnotes
 * @param bool p_copy_monitoring_users
 * @return int representing the new bugid
 * @access public
 */
function bug_copy( $p_bug_id, $p_target_project_id = null, $p_copy_custom_fields = false, $p_copy_relationships = false, $p_copy_history = false, $p_copy_attachments = false, $p_copy_bugnotes = false, $p_copy_monitoring_users = false ) {
	global $g_db;

	$t_mantis_custom_field_string_table = db_get_table( 'mantis_custom_field_string_table' );
	$t_mantis_bug_file_table = db_get_table( 'mantis_bug_file_table' );
	$t_mantis_bugnote_table = db_get_table( 'mantis_bugnote_table' );
	$t_mantis_bugnote_text_table = db_get_table( 'mantis_bugnote_text_table' );
	$t_mantis_bug_history_table = db_get_table( 'mantis_bug_history_table' );
	$t_mantis_db = $g_db;

	$t_bug_id = db_prepare_int( $p_bug_id );
	$t_target_project_id = db_prepare_int( $p_target_project_id );

	$t_bug_data = bug_get( $t_bug_id, true );

	# retrieve the project id associated with the bug
	if(( $p_target_project_id == null ) || is_blank( $p_target_project_id ) ) {
		$t_target_project_id = $t_bug_data->project_id;
	}

	$t_bug_data->project_id = $t_target_project_id;
	$t_bug_data->reporter_id = auth_get_current_user_id();

	$t_new_bug_id = $t_bug_data->create();

	# MASC ATTENTION: IF THE SOURCE BUG HAS TO HANDLER THE bug_create FUNCTION CAN TRY TO AUTO-ASSIGN THE BUG
	# WE FORCE HERE TO DUPLICATE THE SAME HANDLER OF THE SOURCE BUG
	# @todo VB: Shouldn't we check if the handler in the source project is also a handler in the destination project?
	bug_set_field( $t_new_bug_id, 'handler_id', $t_bug_data->handler_id );

	bug_set_field( $t_new_bug_id, 'duplicate_id', $t_bug_data->duplicate_id );
	bug_set_field( $t_new_bug_id, 'status', $t_bug_data->status );
	bug_set_field( $t_new_bug_id, 'resolution', $t_bug_data->resolution );
	bug_set_field( $t_new_bug_id, 'projection', $t_bug_data->projection );
	bug_set_field( $t_new_bug_id, 'eta', $t_bug_data->eta );
	bug_set_field( $t_new_bug_id, 'fixed_in_version', $t_bug_data->fixed_in_version );
	bug_set_field( $t_new_bug_id, 'target_version', $t_bug_data->target_version );
	bug_set_field( $t_new_bug_id, 'sponsorship_total', 0 );
	bug_set_field( $t_new_bug_id, 'sticky', 0 );
	bug_set_field( $t_new_bug_id, 'due_date', $t_bug_data->due_date );

	# COPY CUSTOM FIELDS
	if( $p_copy_custom_fields ) {
		$query = "SELECT field_id, bug_id, value
					   FROM $t_mantis_custom_field_string_table
					   WHERE bug_id=" . db_param();
		$result = db_query_bound( $query, Array( $t_bug_id ) );
		$t_count = db_num_rows( $result );

		for( $i = 0;$i < $t_count;$i++ ) {
			$t_bug_custom = db_fetch_array( $result );

			$c_field_id = db_prepare_int( $t_bug_custom['field_id'] );
			$c_new_bug_id = db_prepare_int( $t_new_bug_id );
			$c_value = $t_bug_custom['value'];

			$query = "INSERT INTO $t_mantis_custom_field_string_table
						   ( field_id, bug_id, value )
						   VALUES (" . db_param() . ', ' . db_param() . ', ' . db_param() . ')';
			db_query_bound( $query, Array( $c_field_id, $c_new_bug_id, $c_value ) );
		}
	}

	# Copy Relationships
	if( $p_copy_relationships ) {
		relationship_copy_all( $t_bug_id, $t_new_bug_id );
	}

	# Copy bugnotes
	if( $p_copy_bugnotes ) {
		$query = "SELECT *
					  FROM $t_mantis_bugnote_table
					  WHERE bug_id=" . db_param();
		$result = db_query_bound( $query, Array( $t_bug_id ) );
		$t_count = db_num_rows( $result );

		for( $i = 0;$i < $t_count;$i++ ) {
			$t_bug_note = db_fetch_array( $result );
			$t_bugnote_text_id = $t_bug_note['bugnote_text_id'];

			$query2 = "SELECT *
						   FROM $t_mantis_bugnote_text_table
						   WHERE id=" . db_param();
			$result2 = db_query_bound( $query2, Array( $t_bugnote_text_id ) );
			$t_count2 = db_num_rows( $result2 );

			$t_bugnote_text_insert_id = -1;
			if( $t_count2 > 0 ) {
				$t_bugnote_text = db_fetch_array( $result2 );

				$query2 = "INSERT INTO $t_mantis_bugnote_text_table
							   ( note )
							   VALUES ( " . db_param() . ' )';
				db_query_bound( $query2, Array( $t_bugnote_text['note'] ) );
				$t_bugnote_text_insert_id = db_insert_id( $t_mantis_bugnote_text_table );
			}

			$query2 = "INSERT INTO $t_mantis_bugnote_table
						   ( bug_id, reporter_id, bugnote_text_id, view_state, date_submitted, last_modified )
						   VALUES ( " . db_param() . ",
						   			" . db_param() . ",
						   			" . db_param() . ",
						   			" . db_param() . ",
						   			" . db_param() . ",
						   			" . db_param() . ')';
			db_query_bound( $query2, Array( $t_new_bug_id, $t_bug_note['reporter_id'], $t_bugnote_text_insert_id, $t_bug_note['view_state'], $t_bug_note['date_submitted'], $t_bug_note['last_modified'] ) );
		}
	}

	# Copy attachments
	if( $p_copy_attachments ) {
	    file_copy_attachments( $t_bug_id , $t_new_bug_id );
	}

	# Copy users monitoring bug
	if( $p_copy_monitoring_users ) {
		bug_monitor_copy( $t_bug_id, $t_new_bug_id );
	}

	# COPY HISTORY
	history_delete( $t_new_bug_id );	# should history only be deleted inside the if statement below?
	if( $p_copy_history ) {
		# @todo problem with this code: the generated history trail is incorrect because the note IDs are those of the original bug, not the copied ones
		# @todo actually, does it even make sense to copy the history ?
		$query = "SELECT *
					  FROM $t_mantis_bug_history_table
					  WHERE bug_id = " . db_param();
		$result = db_query_bound( $query, Array( $t_bug_id ) );
		$t_count = db_num_rows( $result );

		for( $i = 0;$i < $t_count;$i++ ) {
			$t_bug_history = db_fetch_array( $result );
			$query = "INSERT INTO $t_mantis_bug_history_table
						  ( user_id, bug_id, date_modified, field_name, old_value, new_value, type )
						  VALUES ( " . db_param() . ",
						  		   " . db_param() . ",
						  		   " . db_param() . ",
						  		   " . db_param() . ",
						  		   " . db_param() . ",
						  		   " . db_param() . ",
						  		   " . db_param() . " );";
			db_query_bound( $query, Array( $t_bug_history['user_id'], $t_new_bug_id, $t_bug_history['date_modified'], $t_bug_history['field_name'], $t_bug_history['old_value'], $t_bug_history['new_value'], $t_bug_history['type'] ) );
		}
	} else {
		# Create a "New Issue" history entry
		history_log_event_special( $t_new_bug_id, NEW_BUG );
	}


	# Create history entries to reflect the copy operation
	history_log_event_special( $t_new_bug_id, BUG_CREATED_FROM, '', $t_bug_id );
	history_log_event_special( $t_bug_id, BUG_CLONED_TO, '', $t_new_bug_id );

	return $t_new_bug_id;
}

/**
 * Moves an issue from a project to another.
 *
 * @todo Validate with sub-project / category inheritance scenarios.
 * @param int p_bug_id The bug to be moved.
 * @param int p_target_project_id The target project to move the bug to.
 * @access public
 */
function bug_move( $p_bug_id, $p_target_project_id ) {
	// Attempt to move disk based attachments to new project file directory.
	file_move_bug_attachments( $p_bug_id, $p_target_project_id );

	// Move the issue to the new project.
	bug_set_field( $p_bug_id, 'project_id', $p_target_project_id );

	// Update the category if needed
	$t_category_id = bug_get_field( $p_bug_id, 'category_id' );

	// Bug has no category
	if( $t_category_id == 0 ) {
		// Category is required in target project, set it to default
		if( ON != config_get( 'allow_no_category', null, null, $p_target_project_id ) ) {
			bug_set_field( $p_bug_id, 'category_id', config_get( 'default_category_for_moves' ) );
		}
	}
	// Check if the category is global, and if not attempt mapping it to the new project
	else {
		$t_category_project_id = category_get_field( $t_category_id, 'project_id' );

		if ( $t_category_project_id != ALL_PROJECTS
		  && !project_hierarchy_inherit_parent( $p_target_project_id, $t_category_project_id )
		) {
			// Map by name
			$t_category_name = category_get_field( $t_category_id, 'name' );
			$t_target_project_category_id = category_get_id_by_name( $t_category_name, $p_target_project_id, /* triggerErrors */ false );
			if ( $t_target_project_category_id === false ) {
				// Use default category after moves, since there is no match by name.
				$t_target_project_category_id = config_get( 'default_category_for_moves' );
			}
			bug_set_field( $p_bug_id, 'category_id', $t_target_project_category_id );
		}
	}
}

/**
 * allows bug deletion :
 * delete the bug, bugtext, bugnote, and bugtexts selected
 * @param array p_bug_id integer representing bug id
 * @return bool (always true)
 * @access public
 */
function bug_delete( $p_bug_id ) {
	$c_bug_id = (int) $p_bug_id;
	$t_bug_table = db_get_table( 'mantis_bug_table' );
	$t_bug_text_table = db_get_table( 'mantis_bug_text_table' );

	# call pre-deletion custom function
	helper_call_custom_function( 'issue_delete_validate', array( $p_bug_id ) );

	# log deletion of bug
	#history_log_event_special( $p_bug_id, BUG_DELETED, bug_format_id( $p_bug_id ) );

	#email_bug_deleted( $p_bug_id );

	# call post-deletion custom function.  We call this here to allow the custom function to access the details of the bug before
	# they are deleted from the database given it's id.  The other option would be to move this to the end of the function and
	# provide it with bug data rather than an id, but this will break backward compatibility.
	helper_call_custom_function( 'issue_delete_notify', array( $p_bug_id ) );

	# Unmonitor bug for all users
	bug_unmonitor( $p_bug_id, null );

	# Delete custom fields
	custom_field_delete_all_values( $p_bug_id );

	# Delete bugnotes
	bugnote_delete_all( $p_bug_id );

	# Delete all sponsorships
	sponsorship_delete_all( $p_bug_id );

	# MASC RELATIONSHIP
	# we delete relationships even if the feature is currently off.
	relationship_delete_all( $p_bug_id );

	# MASC RELATIONSHIP
	# Delete files
	file_delete_attachments( $p_bug_id );

	# Detach tags
	tag_bug_detach_all( $p_bug_id, false );

	# Delete the bug history
	#history_delete( $p_bug_id );

	# Delete bug info revisions
	bug_revision_delete( $p_bug_id );

	# Delete the bugnote text
	#$t_bug_text_id = bug_get_field( $p_bug_id, 'bug_text_id' );

	$query = "DELETE FROM $t_bug_text_table
				  WHERE dossier=" . db_param();
	db_query_bound( $query, Array( $p_bug_id ) );

	
	# Delete the bug entry
	$query = "DELETE FROM $t_bug_table
				  WHERE id=" . db_param();
	db_query_bound( $query, Array( $c_bug_id ) );

	bug_clear_cache( $p_bug_id );
	bug_text_clear_cache( $p_bug_id );

	# db_query errors on failure so:
	return true;
}

/**
 * Delete all bugs associated with a project
 * @param array p_project_id integer representing a projectid
 * @return bool always true
 * @access public
 * @uses database_api.php
 */
function bug_delete_all( $p_project_id ) {
	$c_project_id = (int) $p_project_id;

	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$query = "SELECT id
				  FROM $t_bug_table
				  WHERE project_id=" . db_param();
	$result = db_query_bound( $query, array( $c_project_id ) );

	$bug_count = db_num_rows( $result );

	for( $i = 0;$i < $bug_count;$i++ ) {
		$row = db_fetch_array( $result );

		bug_delete( $row['id'] );
	}

	# @todo should we check the return value of each bug_delete() and
	#  return false if any of them return false? Presumable bug_delete()
	#  will eventually trigger an error on failure so it won't matter...

	return true;
}

/**
 * Returns the extended record of the specified bug, this includes
 * the bug text fields
 * @todo include reporter name and handler name, the problem is that
 *      handler can be 0, in this case no corresponding name will be
 *      found.  Use equivalent of (+) in Oracle.
 * @param int p_bug_id integer representing bug id
 * @return array
 * @access public
 */
function bug_get_extended_row( $p_bug_id ) {
	$t_base = bug_cache_row( $p_bug_id );
	$t_text = bug_text_cache_row( $p_bug_id );
	# merge $t_text first so that the 'id' key has the bug id not the bug text id
	if(is_array($t_text)){
	return array_merge( $t_text, $t_base );}
}

/**
 * Returns the record of the specified bug
 * @param int p_bug_id integer representing bug id
 * @return array
 * @access public
 */
function bug_get_row( $p_bug_id ) {
	return bug_cache_row( $p_bug_id );
}

/**
 * Returns an object representing the specified bug
 * @param int p_bug_id integer representing bug id
 * @param bool p_get_extended included extended information (including bug_text)
 * @return object BugData Object
 * @access public
 */
function bug_get( $p_bug_id, $p_get_extended = false ) {
	if( $p_get_extended ) {
		$row = bug_get_extended_row( $p_bug_id );
	} else {
		$row = bug_get_row( $p_bug_id );
	}

	$t_bug_data = new BugData;
	$t_bug_data->loadrow( $row );
	return $t_bug_data;

}

function bug_row_to_object( $p_row ) {
	$t_bug_data = new BugData;
	$t_bug_data->loadrow( $p_row );
	return $t_bug_data;
}

/**
 * return the specified field of the given bug
 *  if the field does not exist, display a warning and return ''
 * @param int p_bug_id integer representing bug id
 * @param string p_fieldname field name
 * @return string
 * @access public
 */
function bug_get_field( $p_bug_id, $p_field_name ) {
	$row = bug_get_row( $p_bug_id );

	if( isset( $row[$p_field_name] ) ) {
		return $row[$p_field_name];
	} else {
		error_parameters( $p_field_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}
}

/**
 * return the specified text field of the given bug
 *  if the field does not exist, display a warning and return ''
 * @param int p_bug_id integer representing bug id
 * @param string p_fieldname field name
 * @return string
 * @access public
 */
function bug_get_text_field( $p_bug_id, $p_field_name ) {
	$row = bug_text_cache_row( $p_bug_id );

	if( isset( $row[$p_field_name] ) ) {
		return $row[$p_field_name];
	} else {
		error_parameters( $p_field_name );
		trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
		return '';
	}
}

/**
 * return the bug summary
 *  this is a wrapper for the custom function
 * @param int p_bug_id integer representing bug id
 * @param int p_context representing SUMMARY_CAPTION, SUMMARY_FIELD
 * @return string
 * @access public
 * @uses helper_api.php
 */
function bug_format_summary( $p_bug_id, $p_context ) {
	return helper_call_custom_function( 'format_issue_summary', array( $p_bug_id, $p_context ) );
}

/**
 * return the timestamp for the most recent time at which a bugnote
 *  associated with the bug was modified
 * @param int p_bug_id integer representing bug id
 * @return bool|int false or timestamp in integer format representing newest bugnote timestamp
 * @access public
 * @uses database_api.php
 */
function bug_get_newest_bugnote_timestamp( $p_bug_id ) {
	$c_bug_id = db_prepare_int( $p_bug_id );
	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );

	$query = "SELECT last_modified
				  FROM $t_bugnote_table
				  WHERE bug_id=" . db_param() . "
				  ORDER BY last_modified DESC";
	$result = db_query_bound( $query, Array( $c_bug_id ), 1 );
	$row = db_result( $result );

	if( false === $row ) {
		return false;
	} else {
		return $row;
	}
}

/**
 * return the timestamp for the most recent time at which a bugnote
 *  associated with the bug was modified and the total bugnote
 *  count in one db query
 * @param int p_bug_id integer representing bug id
 * @return object consisting of bugnote stats
 * @access public
 * @uses database_api.php
 */
function bug_get_bugnote_stats( $p_bug_id ) {
	global $g_cache_bug;
	$c_bug_id = db_prepare_int( $p_bug_id );

	if( !is_null( $g_cache_bug[$c_bug_id]['_stats'] ) ) {
		if( $g_cache_bug[$c_bug_id]['_stats'] === false ) {
			return false;
		} else {
			$t_stats = $g_cache_bug[$c_bug_id]['_stats'];
		}
		return $t_stats;
	}

	$t_bugnote_table = db_get_table( 'mantis_bugnote_table' );

	$query = "SELECT last_modified
				  FROM $t_bugnote_table
				  WHERE bug_id=" . db_param() . "
				  ORDER BY last_modified DESC";
	$result = db_query_bound( $query, Array( $c_bug_id ) );
	$row = db_fetch_array( $result );

	if( false === $row ) {
		return false;
	}

	$t_stats['last_modified'] = $row['last_modified'];
	$t_stats['count'] = db_num_rows( $result );

	return $t_stats;
}

/**
 * Get array of attachments associated with the specified bug id.  The array will be
 * sorted in terms of date added (ASC).  The array will include the following fields:
 * id, title, diskfile, filename, filesize, file_type, date_added, user_id.
 * @param int p_bug_id integer representing bug id
 * @return array array of results or null
 * @access public
 * @uses database_api.php
 * @uses file_api.php
 */
function bug_get_attachments( $p_bug_id ) {
	$c_bug_id = db_prepare_int( $p_bug_id );

	$t_bug_file_table = db_get_table( 'mantis_bug_file_table' );

	$query = "SELECT id, title, diskfile, filename, filesize, file_type, date_added, user_id
		                FROM $t_bug_file_table
		                WHERE bug_id=" . db_param() . "
		                ORDER BY date_added";
	$db_result = db_query_bound( $query, Array( $c_bug_id ) );
	$num_files = db_num_rows( $db_result );

	$t_result = array();

	for( $i = 0;$i < $num_files;$i++ ) {
		$t_result[] = db_fetch_array( $db_result );
	}

	return $t_result;
}

# ===================================
# Data Modification
# ===================================
/**
 * Set the value of a bug field
 * @param int p_bug_id integer representing bug id
 * @param string p_field_name pre-defined field name
 * @param any p_value value to set
 * @return bool (always true)
 * @access public
 * @uses database_api.php
 * @uses history_api.php
 */
function bug_set_field( $p_bug_id, $p_field_name, $p_value ) {
	$c_bug_id = db_prepare_int( $p_bug_id );
	$c_value = null;

	switch( $p_field_name ) {

		# bool
		case 'sticky':
			$c_value = $p_value;
			break;

		# int
		case 'project_id':
		case 'reporter_id':
		case 'handler_id':
		case 'duplicate_id':
		#case 'motif_contact':
		case 'sexe':
		case 'type_contact':
		case 'status':
		case 'resolution':
		case 'projection':
		case 'category_id':
		case 'eta':
		case 'view_state':
		case 'profile_id':
		case 'sponsorship_total':
			$c_value = (int) $p_value;
			break;

		# string
		case 'prenom':
		case 'telephone':
		case 'nom':
		case 'nomepouse':
		case 'version':
		case 'fixed_in_version':
		case 'target_version':
		case 'build':
		case 'summary':
			$c_value = $p_value;
			break;

		# dates
		case 'last_updated':
		case 'date_submitted':
		case 'due_date':
			if ( !is_numeric( $p_value ) ) {
				trigger_error( ERROR_GENERIC, ERROR );
			}
			$c_value = $p_value;
			break;

		default:
			trigger_error( ERROR_DB_FIELD_NOT_FOUND, WARNING );
			break;
	}

	$t_current_value = bug_get_field( $p_bug_id, $p_field_name );

	# return if status is already set
	if( $c_value == $t_current_value ) {
		return true;
	}
	$t_bug_table = db_get_table( 'mantis_bug_table' );

	# Update fields
	$query = "UPDATE $t_bug_table
				  SET $p_field_name=" . db_param() . "
				  WHERE id=" . db_param();
	db_query_bound( $query, Array( $c_value, $c_bug_id ) );

	# updated the last_updated date
	bug_update_date( $p_bug_id );

	# log changes except for duplicate_id which is obsolete and should be removed in
	# MantisBT 1.3.
	switch( $p_field_name ) {
		case 'duplicate_id':
			break;

		case 'category_id':
			history_log_event_direct( $p_bug_id, 'category', category_full_name( $t_current_value, false ), category_full_name( $c_value, false ) );
			break;

		default:
			history_log_event_direct( $p_bug_id, $p_field_name, $t_current_value, $c_value );
	}

	bug_clear_cache( $p_bug_id );

	return true;
}

/**
 * assign the bug to the given user
 * @param array p_bug_id_array integer array representing bug ids to cache
 * @return null
 * @access public
 * @uses database_api.php
 */
function bug_assign( $p_bug_id, $p_user_id, $p_bugnote_text = '', $p_bugnote_private = false ) {
	$c_bug_id = db_prepare_int( $p_bug_id );
	$c_user_id = db_prepare_int( $p_user_id );

	if(( $c_user_id != NO_USER ) && !access_has_bug_level( config_get( 'handle_bug_threshold' ), $p_bug_id, $p_user_id ) ) {
		trigger_error( ERROR_USER_DOES_NOT_HAVE_REQ_ACCESS );
	}

	# extract current information into history variables
	$h_status = bug_get_field( $p_bug_id, 'status' );
	$h_handler_id = bug_get_field( $p_bug_id, 'handler_id' );

	if(( ON == config_get( 'auto_set_status_to_assigned' ) ) && ( NO_USER != $p_user_id ) ) {
		$t_ass_val = config_get( 'bug_assigned_status' );
	} else {
		$t_ass_val = $h_status;
	}

	$t_bug_table = db_get_table( 'mantis_bug_table' );

	if(( $t_ass_val != $h_status ) || ( $p_user_id != $h_handler_id ) ) {

		# get user id
		$query = "UPDATE $t_bug_table
					  SET handler_id=" . db_param() . ", status=" . db_param() . "
					  WHERE id=" . db_param();
		db_query_bound( $query, Array( $c_user_id, $t_ass_val, $c_bug_id ) );

		# log changes
		history_log_event_direct( $c_bug_id, 'status', $h_status, $t_ass_val );
		history_log_event_direct( $c_bug_id, 'handler_id', $h_handler_id, $p_user_id );

		# Add bugnote if supplied ignore false return
		bugnote_add( $p_bug_id, $p_bugnote_text, 0, $p_bugnote_private, 0, '', NULL, FALSE );

		# updated the last_updated date
		bug_update_date( $p_bug_id );

		bug_clear_cache( $p_bug_id );

		# send assigned to email
		email_assign( $p_bug_id );
	}

	return true;
}

/**
 * close the given bug
 * @param int p_bug_id
 * @param string p_bugnote_text
 * @param bool p_bugnote_private
 * @param string p_time_tracking
 * @return bool (always true)
 * @access public
 */
function bug_close( $p_bug_id, $p_bugnote_text = '', $p_bugnote_private = false, $p_time_tracking = '0:00' ) {
	$p_bugnote_text = trim( $p_bugnote_text );

	# Add bugnote if supplied ignore a false return
	# Moved bugnote_add before bug_set_field calls in case time_tracking_no_note is off.
	# Error condition stopped execution but status had already been changed
	bugnote_add($f_bes_com,$f_bes_soc,$f_bes_vio,$f_nat_vio_cpl,$f_enf_temoin_cpl,$f_enf_vio_cpl,$f_comm_cpl,$f_cond_trav,$f_isol_trav,$f_dign_trav,$f_vio_trav,$f_juri_trav,$f_domaine,$f_nom_vio,$f_prenom_vio,$f_age_vio,$f_sexe_vio,$f_adresse_vio,$f_emploi_vio,$f_ressources_vio,$f_secteur_vio,$f_csp_vio,$f_lien_vict_vio,$f_vict_enf_vio,$f_mere_vict_vio,$f_pb_vio,$f_comm_vio,$f_cons_phys,$f_cons_psy,$f_cons_trav,$f_cons_soc,$f_cons_comm,$f_dur_violence, $f_lieu_violence, $f_bug_id, $f_time_tracking, $f_private, BUGNOTE);

	bug_set_field( $p_bug_id, 'status', config_get( 'bug_closed_status_threshold' ) );

	email_close( $p_bug_id );
	email_relationship_child_closed( $p_bug_id );

	return true;
}

/**
 * resolve the given bug
 * @param int p_bug_id
 * @param int p_resolution resolution code
 * @param int p_status optional custom status (defaults to bug_resolved_status_threshold)
 * @param string p_fixed_in_version optional version string in which issue is fixed
 * @param int p_duplicate_id optional id of duplicate issue (defaults to null)
 * @param int p_handler_id optional id of issue handler
 * @param string p_bugnote_text optional bug note to add
 * @param bool p_bugnote_private optional true if bug note should be private (defaults to false)
 * @param string p_time_tracking optional time spent (defaults to '0:00')
 * @return bool (always true)
 * @access public
 */
function bug_resolve( $p_bug_id, $p_resolution, $p_status = null, $p_fixed_in_version = '', $p_duplicate_id = null, $p_handler_id = null, $p_bugnote_text = '', $p_bugnote_private = false, $p_time_tracking = '0:00' ) {
	$c_resolution = (int) $p_resolution;
	if( null == $p_status ) {
		$p_status = config_get( 'bug_resolved_status_threshold' );
	}
	$p_bugnote_text = trim( $p_bugnote_text );

	# Add bugnote if supplied
	# Moved bugnote_add before bug_set_field calls in case time_tracking_no_note is off.
	# Error condition stopped execution but status had already been changed
	bugnote_add( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, 0, '', NULL, FALSE );

	$t_duplicate = !is_blank( $p_duplicate_id ) && ( $p_duplicate_id != 0 );
	if( $t_duplicate ) {
		if( $p_bug_id == $p_duplicate_id ) {
			trigger_error( ERROR_BUG_DUPLICATE_SELF, ERROR );

			# never returns
		}

		# the related bug exists...
		bug_ensure_exists( $p_duplicate_id );

		# check if there is other relationship between the bugs...
		$t_id_relationship = relationship_same_type_exists( $p_bug_id, $p_duplicate_id, BUG_DUPLICATE );

		 if( $t_id_relationship > 0 ) {
			# Update the relationship
			relationship_update( $t_id_relationship, $p_bug_id, $p_duplicate_id, BUG_DUPLICATE );

			# Add log line to the history (both bugs)
			history_log_event_special( $p_bug_id, BUG_REPLACE_RELATIONSHIP, BUG_DUPLICATE, $p_duplicate_id );
			history_log_event_special( $p_duplicate_id, BUG_REPLACE_RELATIONSHIP, BUG_HAS_DUPLICATE, $p_bug_id );
		} else if ( $t_id_relationship != -1 ) {
			# Add the new relationship
			relationship_add( $p_bug_id, $p_duplicate_id, BUG_DUPLICATE );

			# Add log line to the history (both bugs)
			history_log_event_special( $p_bug_id, BUG_ADD_RELATIONSHIP, BUG_DUPLICATE, $p_duplicate_id );
			history_log_event_special( $p_duplicate_id, BUG_ADD_RELATIONSHIP, BUG_HAS_DUPLICATE, $p_bug_id );
		} # else relationship is -1 - same type exists, do nothing

		# Copy list of users monitoring the duplicate bug to the original bug
		bug_monitor_copy( $p_bug_id, $p_duplicate_id );

		bug_set_field( $p_bug_id, 'duplicate_id', (int) $p_duplicate_id );
	}

	bug_set_field( $p_bug_id, 'status', $p_status );
	bug_set_field( $p_bug_id, 'fixed_in_version', $p_fixed_in_version );
	bug_set_field( $p_bug_id, 'resolution', $c_resolution );

	# only set handler if specified explicitly or if bug was not assigned to a handler
	if( null == $p_handler_id ) {
		if( bug_get_field( $p_bug_id, 'handler_id' ) == 0 ) {
			$p_handler_id = auth_get_current_user_id();
			bug_set_field( $p_bug_id, 'handler_id', $p_handler_id );
		}
	} else {
		bug_set_field( $p_bug_id, 'handler_id', $p_handler_id );
	}

	email_resolved( $p_bug_id );
	email_relationship_child_resolved( $p_bug_id );

	if( $c_resolution >= config_get( 'bug_resolution_fixed_threshold' ) &&
		$c_resolution < config_get( 'bug_resolution_not_fixed_threshold' ) ) {
		twitter_issue_resolved( $p_bug_id );
	}

	return true;
}

/**
 * reopen the given bug
 * @param int p_bug_id
 * @param string p_bugnote_text
 * @param string p_time_tracking
 * @param bool p_bugnote_private
 * @return bool (always true)
 * @access public
 * @uses database_api.php
 * @uses email_api.php
 * @uses bugnote_api.php
 * @uses config_api.php
 */
function bug_reopen( $p_bug_id, $p_bugnote_text = '', $p_time_tracking = '0:00', $p_bugnote_private = false ) {
	$p_bugnote_text = trim( $p_bugnote_text );

	# Add bugnote if supplied
	# Moved bugnote_add before bug_set_field calls in case time_tracking_no_note is off.
	# Error condition stopped execution but status had already been changed
	bugnote_add( $p_bug_id, $p_bugnote_text, $p_time_tracking, $p_bugnote_private, 0, '', NULL, FALSE );

	bug_set_field( $p_bug_id, 'status', config_get( 'bug_reopen_status' ) );
	bug_set_field( $p_bug_id, 'resolution', config_get( 'bug_reopen_resolution' ) );

	email_reopen( $p_bug_id );

	return true;
}

/**
 * updates the last_updated field
 * @param int p_bug_id integer representing bug ids
 * @return bool (always true)
 * @access public
 * @uses database_api.php
 */
function bug_update_date( $p_bug_id ) {
	$c_bug_id = (int) $p_bug_id;

	$t_bug_table = db_get_table( 'mantis_bug_table' );

	$query = "UPDATE $t_bug_table
				  SET last_updated= " . db_param() . "
				  WHERE id=" . db_param();
	db_query_bound( $query, Array( db_now(), $c_bug_id ) );

	bug_clear_cache( $c_bug_id );

	return true;
}

/**
 * enable monitoring of this bug for the user
 * @param int p_bug_id integer representing bug ids
 * @param int p_user_id integer representing user ids
 * @return true if successful, false if unsuccessful
 * @access public
 * @uses database_api.php
 * @uses history_api.php
 * @uses user_api.php
 */
function bug_monitor( $p_bug_id, $p_user_id ) {
	$c_bug_id = (int) $p_bug_id;
	$c_user_id = (int) $p_user_id;

	# Make sure we aren't already monitoring this bug
	if( user_is_monitoring_bug( $c_user_id, $c_bug_id ) ) {
		return true;
	}

	# Don't let the anonymous user monitor bugs
	if ( user_is_anonymous( $c_user_id ) ) {
		return false;
	}

	$t_bug_monitor_table = db_get_table( 'mantis_bug_monitor_table' );

	# Insert monitoring record
	$query = 'INSERT INTO ' . $t_bug_monitor_table . '( user_id, bug_id ) VALUES (' . db_param() . ',' . db_param() . ')';
	db_query_bound( $query, Array( $c_user_id, $c_bug_id ) );

	# log new monitoring action
	#history_log_event_special( $c_bug_id, BUG_MONITOR, $c_user_id );

	# updated the last_updated date
	bug_update_date( $p_bug_id );

	#email_monitor_added( $p_bug_id, $p_user_id );

	return true;
}

/**
 * Returns the list of users monitoring the specified bug
 *
 * @param int $p_bug_id
 */
function bug_get_monitors( $p_bug_id ) {

    if ( ! access_has_bug_level( config_get( 'show_monitor_list_threshold' ), $p_bug_id ) ) {
        return Array();
    }


	$c_bug_id = db_prepare_int( $p_bug_id );
	$t_bug_monitor_table = db_get_table( 'mantis_bug_monitor_table' );
	$t_user_table = db_get_table( 'mantis_user_table' );

	# get the bugnote data
	$query = "SELECT user_id, enabled
			FROM $t_bug_monitor_table m, $t_user_table u
			WHERE m.bug_id=" . db_param() . " AND m.user_id = u.id
			ORDER BY u.realname, u.username";
	$result = db_query_bound($query, Array( $c_bug_id ) );
	$num_users = db_num_rows($result);

	$t_users = array();
	for ( $i = 0; $i < $num_users; $i++ ) {
		$row = db_fetch_array( $result );
		$t_users[$i] = $row['user_id'];
	}

	user_cache_array_rows( $t_users );

	return $t_users;
}

/**
 * Copy list of users monitoring a bug to the monitor list of a second bug
 * @param int p_source_bug_id integer representing the bug ID of the source bug
 * @param int p_dest_bug_id integer representing the bug ID of the destination bug
 * @return bool (always true)
 * @access public
 * @uses database_api.php
 * @uses history_api.php
 * @uses user_api.php
 */
function bug_monitor_copy( $p_source_bug_id, $p_dest_bug_id ) {
	$c_source_bug_id = (int)$p_source_bug_id;
	$c_dest_bug_id = (int)$p_dest_bug_id;

	$t_bug_monitor_table = db_get_table( 'mantis_bug_monitor_table' );

	$query = 'SELECT user_id
		FROM ' . $t_bug_monitor_table . '
		WHERE bug_id = ' . db_param();
	$result = db_query_bound( $query, Array( $c_source_bug_id ) );
	$t_count = db_num_rows( $result );

	for( $i = 0; $i < $t_count; $i++ ) {
		$t_bug_monitor = db_fetch_array( $result );
		if ( !user_is_monitoring_bug( $t_bug_monitor['user_id'], $c_dest_bug_id ) ) {
			$query = 'INSERT INTO ' . $t_bug_monitor_table . ' ( user_id, bug_id )
				VALUES ( ' . db_param() . ', ' . db_param() . ' )';
			db_query_bound( $query, Array( $t_bug_monitor['user_id'], $c_dest_bug_id ) );
			history_log_event_special( $c_dest_bug_id, BUG_MONITOR, $t_bug_monitor['user_id'] );
		}
	}
}

/**
 * disable monitoring of this bug for the user
 * if $p_user_id = null, then bug is unmonitored for all users.
 * @param int p_bug_id integer representing bug ids
 * @param int p_user_id integer representing user ids
 * @return bool (always true)
 * @access public
 * @uses database_api.php
 * @uses history_api.php
 */
function bug_unmonitor( $p_bug_id, $p_user_id ) {
	$c_bug_id = (int) $p_bug_id;
	$c_user_id = (int) $p_user_id;

	$t_bug_monitor_table = db_get_table( 'mantis_bug_monitor_table' );

	# Delete monitoring record
	$query = 'DELETE FROM ' . $t_bug_monitor_table . ' WHERE bug_id = ' . db_param();
	$db_query_params[] = $c_bug_id;

	if( $p_user_id !== null ) {
		$query .= " AND user_id = " . db_param();
		$db_query_params[] = $c_user_id;
	}

	db_query_bound( $query, $db_query_params );

	# log new un-monitor action
	#history_log_event_special( $c_bug_id, BUG_UNMONITOR, $c_user_id );

	# updated the last_updated date
	bug_update_date( $p_bug_id );

	return true;
}

/**
 * Pads the bug id with the appropriate number of zeros.
 * @param int p_bug_id
 * @return string
 * @access public
 * @uses config_api.php
 */
function bug_format_id( $p_bug_id ) {
	$t_padding = config_get( 'display_bug_padding' );
	$t_string = utf8_str_pad( $p_bug_id, $t_padding, '0', STR_PAD_LEFT );

	return event_signal( 'EVENT_DISPLAY_BUG_ID', $t_string, array( $p_bug_id ) );
}
