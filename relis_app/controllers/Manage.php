<?php
/* ReLiS - A Tool for conducting systematic literature reviews and mapping studies.
 * Copyright (C) 2018  Eugene Syriani
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * --------------------------------------------------------------------------
 *
 *  :Author: Brice Michel Bigendako
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class Manage extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		old_version('from old controller Manage');
	
				
	}
	public function index()
	{
		
		redirect('/manage/liste_ref');
			
	}
	
	
	
	/*
	 * Fonction globale pour afficher la liste des élément suivant la structure de la table
	 * 
	 * Input: $ref_table: nom de la configuration d'une page (ex papers, author)
	 * 			$val : valeur de recherche si une recherche a été faite sur la table en cours
	 * 			$page: la page affiché : ulilisé dans la navigation 
	 */
	public function liste_ref($ref_table='papers',$val = "_", $page = 0){
		
		
		/*
		 * Redirection des tables qui ont des fonctions pérsonnalisés pour l'affichage la liste des élément
		 */
		if($ref_table=='papers'){ 
				
			redirect('manage/list_paper/all/'.$val."/".$page);
		}elseif($ref_table=='classification'){
			redirect('manage/list_classification_dt/normal/'.$val."/".$page);
		}elseif($ref_table=='config'){
			redirect('manage/view_ref/config/1');
		}elseif($ref_table=='project'){
			redirect('admin/projects_list');
		}
	
	
	
		/*
		 * Vérification si il y a une condition de recherche 
		 */
		$val = urldecode ( urldecode ( $val ) );
		$filter = array ();
		if (isset ( $_POST ['search_all'] )) {
			$filter = $this->input->post ();
			
			unset ( $filter ['search_all'] );
	
			$val = "_";
			if (isset ( $filter ['valeur'] ) and ! empty ( $filter ['valeur'] )) {
				$val = $filter ['valeur'];
				$val = urlencode ( urlencode ( $val ) );
			}
	
			/*
			 * mis à jours de l'url en ajoutant la valeur recherché dans le lien puis rechargement de l'url
			 */
			$url = "manage/liste_ref/" . $ref_table ."/". $val ."/0/";
	
			redirect ( $url );
		}
	
		/*
		 * Récupération de la configuration(structure) de la table à afficher 
		 */
		
		$ref_table_config=$this->ref_table_config($ref_table);
	
		
		$table_id=$ref_table_config['table_id'];
		
		
		$extra_condition="";
		
		/*
		 * Appel du model pour récuperer la liste à afiicher dans la Base de données
		 */
		
		if($ref_table=="str_mng"){ //pour le String_management une fonction spéciale
			
		
			$data=$this->DBConnection_mdl->get_list_str_mng($ref_table_config,$val,$page,0,$this->session->userdata('active_language'));
			
		}else{
			
			$data=$this->DBConnection_mdl->get_list($ref_table_config,$val,$page,0);
		}
		
		
		
		/*
		 * récupération des correspondances des clès externes pour l'affichage  suivant la structure de la table 
		 */
	
		$dropoboxes=array();
		foreach ($ref_table_config['fields'] as $k => $v) {
	
			if(!empty($v['input_type']) AND $v['input_type']=='select' AND $v['on_list']=='show'){
				if($v['input_select_source']=='array'){
					$dropoboxes[$k]=$v['input_select_values'];
				}elseif($v['input_select_source']=='table'){
					$dropoboxes[$k]= $this->get_reference_select_values($v['input_select_values']);
				}
			}
			;
		}
	
		
		
		/*
		 * Vérification des liens (links) a afficher sur la liste
		 */
		$add_child_link=False;
		$edit_link=False;
		$view_link=False;
		$delete_link=False;
		$add_link=True;
	
		//view link
		if(!empty(	$ref_table_config['links']['view']) AND !empty($ref_table_config['links']['view']['on_list'])  AND ($ref_table_config['links']['view']['on_list']== True) ){
	
			if(!empty($ref_table_config['links']['view']['url'])){
				$view_link_url=$ref_table_config['links']['view']['url'];
			}else{
				$view_link_url='manage/view_ref/' . $ref_table;
			}
	
			$view_link_label=$ref_table_config['links']['view']['label'];
			$view_link_title=$ref_table_config['links']['view']['title'];
			$view_link=True;
		}
	
		if(isset(	$ref_table_config['links']['add']) AND !$ref_table_config['links']['add'] ){
			
			$add_link=False;
		}
		
		//edit link
		if(!empty(	$ref_table_config['links']['edit']) AND !empty($ref_table_config['links']['edit']['on_list'])  AND ($ref_table_config['links']['edit']['on_list']== True) ){
			
			$edit_link_label=$ref_table_config['links']['edit']['label'];
			$edit_link_title=$ref_table_config['links']['edit']['title'];
			$edit_link=True;
		}
	
	
		//addchild link
		if(!empty(	$ref_table_config['links']['add_child']['url']) AND !empty($ref_table_config['links']['add_child']['on_list'])  AND ($ref_table_config['links']['add_child']['on_list']== True) ){
			$child_link_url='manage/add_ref_child/'.$ref_table_config['links']['add_child']['url']."/". $ref_table;
			$child_link_label=$ref_table_config['links']['add_child']['label'];
			$child_link_title=$ref_table_config['links']['add_child']['title'];
			$add_child_link=True;
		}
	
		//delete link
		if(!empty(	$ref_table_config['links']['delete']) AND !empty($ref_table_config['links']['delete']['on_list'])  AND ($ref_table_config['links']['delete']['on_list']== True) ){
	
			$delete_link_label=$ref_table_config['links']['delete']['label'];
			$delete_link_title=$ref_table_config['links']['delete']['title'];
			$delete_link=True;
		}
	
		
		
		$i=1;
		
		/*
		 * Préparation de la liste à afficher sur base du contenu et  stucture de la table
		 */
		foreach ($data['list'] as $key => $value) {
			
			/*
			 * Ajout des liens(links) sur la liste
			 */
			$add_child_button="";
			$edit_button="";
			$view_button="";
			$action_button="";
			$arr_buttons=array();
	
			if($view_link){
	
					
				array_push($arr_buttons, array(
						'url'=> $view_link_url.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-folder"></i> '.$view_link_label,
						'title'=>$view_link_title
							
				)	);
				
			}
	
			if($edit_link){
	
	
				array_push($arr_buttons, array(
						'url'=> 'manage/edit_ref/' . $ref_table.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-pencil"></i> '.$edit_link_label,
						'title'=>$edit_link_title
							
				)	);
					
			}
	
			if($add_child_link){
	
				array_push($arr_buttons, array(
						'url'=> $child_link_url.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-plus"></i> '.$child_link_label,
						'title'=>$child_link_title
	
				)	);	
			}
				
			if($delete_link){
					
					
				array_push($arr_buttons, array(
						'url'=> 'manage/delete_ref/' . $ref_table.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-trash"></i> '.$delete_link_label,
						'title'=>$delete_link_title
							
				)	);
				
			}
	
			$action_button=create_button_link_dropdown($arr_buttons);
	
			$data['list'][$key]['links']=$action_button;
			
			$data ['list'] [$key] [$table_id] = $i + $page;
	
			/*
			 * Remplacement des clés externes par leurs correspondances
			 */
			foreach ($dropoboxes as $k => $v) {
				if($data ['list'] [$key] [$k])
					$data ['list'] [$key] [$k]=$v[$data ['list'] [$key] [$k]];
				else
					$data ['list'] [$key] [$k]="";
			}
	
			$i++;
		}
	
		
		/*
		 * Ajout de l'entête de la liste
		 */
		if(!empty($data['list'])){
			$array_header=$ref_table_config['header_list_fields'];
			if(trim($data['list'][$key]['links']) !=""){
				array_push($array_header,'');
			}
			array_unshift($data['list'],$array_header);
		}
		
		
		/*
		 * Création des boutons qui vont s'afficher en haut de la page (top_buttons)
		 */
		$data ['top_buttons']="";
		if($ref_table=="str_mng"){
			if($this->session->userdata('language_edit_mode')=='yes'){
			$data ['top_buttons'] .= get_top_button ( 'all', 'Close edition mode', 'config/update_edition_mode/no','Close edition mode','fa-ban','',' btn-warning ' );
			}else{
				$data ['top_buttons'] .= get_top_button ( 'all', 'Open edition mode', 'config/update_edition_mode/yes','Open edition mode','fa-check','',' btn-dark ' );
			}	
		}elseif($ref_table=='class_scheme'){//for_classification_scheme
			$data ['top_buttons'] .= get_top_button ( 'all', 'Generate code and DB ', 'config/generate_config',' Generate code and DB ','fa-refresh','',' btn-dark ' );
			$data ['top_buttons'] .= get_top_button ( 'add', 'Add free field', 'manage/add_class_scheme/free',' Simple field' );
			$data ['top_buttons'] .= get_top_button ( 'add', 'Add static list', 'manage/add_class_scheme/static',' Static list' );
			$data ['top_buttons'] .= get_top_button ( 'add', 'Add dynamic list', 'manage/add_class_scheme/dynamic',' Dynamic list' );
		}else{
			if($add_link)
			$data ['top_buttons'] .= get_top_button ( 'add', 'Add new', 'manage/add_ref/'.$ref_table );
		}
		$data ['top_buttons'] .= get_top_button ( 'close', 'Close', 'home' );
	
	
		/*
		 * Titre de la page
		 */
		$data['page_title']=$ref_table_config['reference_title'].'';
		
		/*
		 * Configuration pour l'affichage des lien de navigation
		 */
		$data ['nav_pre_link'] = 'manage/liste_ref/' .$ref_table.'/' . $val . '/';
		$data ['nav_page_position'] = 5;
	
		$data ['valeur']=($val=="_")?"":$val;
		
		
		/*
		 * Si on a besoin de faire urecherche sur la liste specifier la vue où se trouve le formulaire de recherche
		 */
		if(!empty($ref_table_config['search_by'])){
			$data ['search_view'] = 'search_view';
		}

		
		/*
		 * La vue qui va s'afficher
		 */
		
		$data['page']='liste';
	
		if(admin_config($ref_table))
			$data['left_menu_admin']=True;
			/*
			 * Chargement de la vue avec les données préparés dans le controleur
			 */
			$this->load->view('body',$data);
	}
	
	
	
	/*
	 * Fonction utilisé pour faire une recherche dans la liste des classifications
	 * 
	 * fields: le champs où on effectue la recherche
	 * $value: la valeur recherché
	 */
	public function search_classification($field,$value){
		
		$condition=array('classification_search_field'=>$field,
				'classification_search_value'=>$value
		);
		
		/*
		 * Chargement des critères de recherche dans une session
		 */
		$this->session->set_userdata($condition);
		
		/*
		 * Appel de la fonction d'affichagage de liste des classification en tenant comptes des critères de recherches mis en session
		 */
		$this->list_classification('search_cat');
		
	}
	
	
	
	/*
	 * Fonction  pour afficher la liste des classifications faites
	 *
	 * Input: $list_type: indique si une recherche à été faites ou pas.  si $list_type= 'normal' on affiche toute la liste
	 * 			$val : valeur de recherche si une recherche a été faite 
	 * 			$page: la page à affiché : ulilisé par les lien de navigation
	 */
	
	public function list_classification($list_type='normal',$val = "_", $page = 0){
		
		// nouvelle fonction pour afficher la liste des classification elle utilise les data tables
		redirect("manage/list_classification_dt/$list_type/$val/$page");
	
		
		$ref_table='classification';
	
		$val = urldecode ( urldecode ( $val ) );
		$filter = array ();
		if (isset ( $_POST ['search_all'] )) {
			$filter = $this->input->post ();
			// print_test($filter);exit;
			unset ( $filter ['search_all'] );
	
			$val = "_";
			if (isset ( $filter ['valeur'] ) and ! empty ( $filter ['valeur'] )) {
				$val = $filter ['valeur'];
				$val = urlencode ( urlencode ( $val ) );
			}
	
	
			$url = "manage/liste_ref/" . $ref_table ."/". $val ."/0/";
	
			redirect ( $url );
		}
	
	
		$ref_table_config=$this->ref_table_config($ref_table);
	
		//	print_test($ref_table_config);
	
		$table_id=$ref_table_config['table_id'];
	
	
		$condition=array();
		$extra_condition="";
		$sup_title="";
	
		if($list_type=='search_cat')
		{
	
			if( $this->session->userdata('classification_search_field') AND $this->session->userdata('classification_search_value') ){
	
				$field=$this->session->userdata('classification_search_field');
				$value=$this->session->userdata('classification_search_value');
	
				$extra_condition =" AND ( ".$field."='".$value."') ";
	
				$value_desc=$value;
	
				if(!empty($ref_table_config['fields'][$field]['input_type']) AND $ref_table_config['fields'][$field]['input_type']=='select' ){
					$values=  $this->get_reference_select_values($ref_table_config['fields'][$field]['input_select_values']);
						
					//	print_test($values);
						
					$value_desc=$values[$value];
						
						
					$sup_title = " for \"". $ref_table_config['fields'][$field]['field_title']."\" :  $value_desc";
				}
	
	
			}
				
			
		}
	
	
		if(!empty($extra_condition)){
				
			$data=$this->manage_mdl->get_list($ref_table_config,$val,$page,0,$extra_condition);
		}else{
			$data=$this->DBConnection_mdl->get_list($ref_table_config,$val,$page,0,$extra_condition);
		}
	
	
	
	
	
		
	
		//for select dropboxes
		//print_test($ref_table_config);
		$dropoboxes=array();
		foreach ($ref_table_config['fields'] as $k => $v) {
	
			if(!empty($v['input_type']) AND $v['input_type']=='select' AND $v['on_list']=='show'){
				if($v['input_select_source']=='array'){
					$dropoboxes[$k]=$v['input_select_values'];
				}elseif($v['input_select_source']=='table'){
					$dropoboxes[$k]= $this->get_reference_select_values($v['input_select_values']);
				}
			}
			;
		}
	
		//print_test($dropoboxes);
		//See links to be added;
		$add_child_link=False;
		$edit_link=False;
		$view_link=False;
		$delete_link=False;
	
		//view link
		if(!empty(	$ref_table_config['links']['view']) AND !empty($ref_table_config['links']['view']['on_list'])  AND ($ref_table_config['links']['view']['on_list']== True) ){
	
			if(!empty($ref_table_config['links']['view']['url'])){
				$view_link_url=$ref_table_config['links']['view']['url'];
			}else{
				$view_link_url='manage/view_ref/' . $ref_table;
			}
	
			$view_link_label=$ref_table_config['links']['view']['label'];
			$view_link_title=$ref_table_config['links']['view']['title'];
			$view_link=True;
		}
	
		//edit link
		if(!empty(	$ref_table_config['links']['edit']) AND !empty($ref_table_config['links']['edit']['on_list'])  AND ($ref_table_config['links']['edit']['on_list']== True) ){
			//$edit_link_url=$ref_table_config['links']['edit']['url'];
			$edit_link_label=$ref_table_config['links']['edit']['label'];
			$edit_link_title=$ref_table_config['links']['edit']['title'];
			$edit_link=True;
		}
	
	
		//addchild link
		if(!empty(	$ref_table_config['links']['add_child']['url']) AND !empty($ref_table_config['links']['add_child']['on_list'])  AND ($ref_table_config['links']['add_child']['on_list']== True) ){
			$child_link_url='manage/add_ref_child/'.$ref_table_config['links']['add_child']['url']."/". $ref_table;
			$child_link_label=$ref_table_config['links']['add_child']['label'];
			$child_link_title=$ref_table_config['links']['add_child']['title'];
			$add_child_link=True;
		}
	
		//delete link
		if(!empty(	$ref_table_config['links']['delete']) AND !empty($ref_table_config['links']['delete']['on_list'])  AND ($ref_table_config['links']['delete']['on_list']== True) ){
	
			$delete_link_label=$ref_table_config['links']['delete']['label'];
			$delete_link_title=$ref_table_config['links']['delete']['title'];
			$delete_link=True;
				
	
		}
	
		$i=1;
	
		//print_test($data);
		foreach ($data['list'] as $key => $value) {
			$add_child_button="";
			$edit_button="";
			$view_button="";
			$action_button="";
			$arr_buttons=array();
	
			if($view_link){
	
					
				array_push($arr_buttons, array(
						'url'=> $view_link_url.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-folder"></i> '.$view_link_label,
						'title'=>$view_link_title
							
				)	);
	
					
					
				///$view_button = create_button_link('manage/view_ref/' . $ref_table.'/'.$value [$table_id],'<i class="fa fa-folder"></i>'.$view_link_label,"btn-primary",$view_link_title);
					
			}
	
			if($edit_link){
	
	
				array_push($arr_buttons, array(
						'url'=> 'manage/edit_ref/' . $ref_table.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-pencil"></i> '.$edit_link_label,
						'title'=>$edit_link_title
							
				)	);
					
				////$edit_button = create_button_link('manage/edit_ref/' . $ref_table.'/'.$value [$table_id],'<i class="fa fa-pencil"></i>'.$edit_link_label,"btn-info",$edit_link_title);
					
			}
	
			if($add_child_link){
	
				array_push($arr_buttons, array(
						'url'=> $child_link_url.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-plus"></i> '.$child_link_label,
						'title'=>$child_link_title
	
				)	);
	
					
					
				///$add_child_button = create_button_link($child_link_url.'/'.$value [$table_id],'<i class="fa fa-plus"></i>'.$child_link_label,"btn-dark",$child_link_title);
					
			}
				
			if($delete_link){
					
					
				array_push($arr_buttons, array(
						'url'=> 'manage/delete_ref/' . $ref_table.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-trash"></i> '.$delete_link_label,
						'title'=>$delete_link_title
							
				)	);
					
				////$edit_button = create_button_link('manage/edit_ref/' . $ref_table.'/'.$value [$table_id],'<i class="fa fa-pencil"></i>'.$edit_link_label,"btn-info",$edit_link_title);
					
			}
	
			$action_button=create_button_link_dropdown($arr_buttons);
	
	
	
			//$data['list'][$key]['edit']=$view_button.$edit_button.$add_child_button.$action_button;
			$data['list'][$key]['edit']=$action_button;
			$data ['list'] [$key] [$table_id] = $i + $page;
			//print_test($dropoboxes);
			foreach ($dropoboxes as $k => $v) {
	
				//	print_test($v);
				if($data ['list'] [$key] [$k]  ){
						
					if( isset($v[$data ['list'] [$key] [$k]]) ){
						if($k=='class_paper_id'){
								
	
							$data['list'][$key][$k]=anchor('manage/view_paper/'.$data ['list'] [$key] [$k],"<u><b>".$v[$data ['list'] [$key] [$k]]."</b></u>");
								
								
						}else{
	
							////ppp
	
							$data ['list'] [$key] [$k]=$v[$data ['list'] [$key] [$k]];
						}
					}
				}
				else
				{$data ['list'] [$key] [$k]="";}
			}
	
			$i++;
		}
	
		//print_test($data);
	
		if(!empty($data['list'])){
			$array_header=$ref_table_config['header_list_fields'];
			if(trim($data['list'][$key]['edit']) !=""){
				array_push($array_header,'');
			}
			array_unshift($data['list'],$array_header);
		}
	
		$data ['top_buttons']="";
		$data ['top_buttons'] .= get_top_button ( 'add', 'Add new', 'manage/add_ref/'.$ref_table );
		$data ['top_buttons'] .= get_top_button ( 'close', 'Close', 'home' );
	
	
	
		//print_test($data['list']);
	
		$data['page_title']=$ref_table_config['reference_title'].$sup_title;
		$data ['nav_pre_link'] = 'manage/list_classification/' .$list_type.'/' . $val . '/';
		$data ['nav_page_position'] = 5;
	
		$data ['valeur']=($val=="_")?"":$val;
		if(!empty($ref_table_config['search_by'])){
			$data ['search_view'] = 'search_papers';}
			$data['page']='liste';
	
	
			$this->load->view('body',$data);
	}
	
	
	
	
	/*
	 * Fonction  pour afficher la liste des classifications utilisant un Java script datatable
	 *
	 * Input: $list_type: indique si une recherche à été faites ou pas.  si $list_type= 'normal' on affiche toute la liste
	 * 			$val : valeur de recherche si une recherche a été faite
	 * 			$page: la page à affiché : ulilisé par les lien de navigation
	 */
	
	public function list_classification_dt($list_type='normal',$val = "_", $page = 0){
		$ref_table='classification';
	
		/*
		 * Vérification si il y a une condition de recherche
		 */
		
		$val = urldecode ( urldecode ( $val ) );
		$filter = array ();
		if (isset ( $_POST ['search_all'] )) {
			$filter = $this->input->post ();
			// print_test($filter);exit;
			unset ( $filter ['search_all'] );
	
			$val = "_";
			if (isset ( $filter ['valeur'] ) and ! empty ( $filter ['valeur'] )) {
				$val = $filter ['valeur'];
				$val = urlencode ( urlencode ( $val ) );
			}
	
			/*
			 * mis à jours de l'url en ajoutant la valeur recherché dans le lien puis rechargement de l'url
			 */
			$url = "manage/liste_ref/" . $ref_table ."/". $val ."/0/";
	
			redirect ( $url );
		}
	
		/*
		 * Récupération de la configuration(structure) de la table à afficher
		 */
		$ref_table_config=$this->ref_table_config($ref_table);
	
		$table_id=$ref_table_config['table_id'];
		
		
		/*
		 * Vérification des critères de  recherche supplementaire 
		 */
		$condition=array();
		$extra_condition="";
		$sup_title="";
		
		if($list_type=='search_cat')
		{

			if( $this->session->userdata('classification_search_field') AND $this->session->userdata('classification_search_value') ){
				
				$field=$this->session->userdata('classification_search_field');
				$value=$this->session->userdata('classification_search_value');
				
				$extra_condition =" AND ( ".$field."='".$value."') ";
				
				$value_desc=$value;
				
				if(!empty($ref_table_config['fields'][$field]['input_type']) AND $ref_table_config['fields'][$field]['input_type']=='select' ){
				
				if($ref_table_config['fields'][$field]['input_select_source']=='table'){
					$values=  $this->get_reference_select_values($ref_table_config['fields'][$field]['input_select_values']);
				}elseif($ref_table_config['fields'][$field]['input_select_source']=='yes_no'){
					$values=array("No",'Yes');
				}
				
					
					$value_desc=$value;
					if(!empty($values[$value]))
					$value_desc=$values[$value];
					
					
					$sup_title = " for \"". $ref_table_config['fields'][$field]['field_title']."\" :  $value_desc";
				}
				
				
			}
			
			
		}
		
		/*
		 * Appel du model pour récuperer la liste à afficher dans la Base de données
		 */
		
		if(!empty($extra_condition)){
			
			$data=$this->manage_mdl->get_list($ref_table_config,$val,$page,-1,$extra_condition);
		}else{
			$data=$this->DBConnection_mdl->get_list($ref_table_config,$val,$page,-1,$extra_condition);
		}
		
		//print_test($data);
		/*
		 * récupération des correspondances des clès externes pour l'affichage  suivant la structure de la table
		 */
		$dropoboxes=array();
		foreach ($ref_table_config['fields'] as $k => $v) {
				
			if(!empty($v['input_type']) AND $v['input_type']=='select' AND $v['on_list']=='show'){
				if($v['input_select_source']=='array'){
					$dropoboxes[$k]=$v['input_select_values'];
				}elseif($v['input_select_source']=='table'){
					$dropoboxes[$k]= $this->get_reference_select_values($v['input_select_values']);
				}elseif($v['input_select_source']=='yes_no'){
					$dropoboxes[$k]=array('0'=>"No",
										'1'=>"Yes"
					);
				}
			}
			;
		}
	
		/*
		 * Vérification des liens (links) a afficher sur la liste
		 */
		$add_child_link=False;
		$edit_link=False;
		$view_link=False;
		$delete_link=False;
	
		//view link
		if(!empty(	$ref_table_config['links']['view']) AND !empty($ref_table_config['links']['view']['on_list'])  AND ($ref_table_config['links']['view']['on_list']== True) ){
				
			if(!empty($ref_table_config['links']['view']['url'])){
				$view_link_url=$ref_table_config['links']['view']['url'];
			}else{
				$view_link_url='manage/view_ref/' . $ref_table;
			}
	
			$view_link_label=$ref_table_config['links']['view']['label'];
			$view_link_title=$ref_table_config['links']['view']['title'];
			$view_link=True;
		}
	
		//edit link
		if(!empty(	$ref_table_config['links']['edit']) AND !empty($ref_table_config['links']['edit']['on_list'])  AND ($ref_table_config['links']['edit']['on_list']== True) ){
			//$edit_link_url=$ref_table_config['links']['edit']['url'];
			$edit_link_label=$ref_table_config['links']['edit']['label'];
			$edit_link_title=$ref_table_config['links']['edit']['title'];
			$edit_link=True;
		}
	
	
		//addchild link
		if(!empty(	$ref_table_config['links']['add_child']['url']) AND !empty($ref_table_config['links']['add_child']['on_list'])  AND ($ref_table_config['links']['add_child']['on_list']== True) ){
			$child_link_url='manage/add_ref_child/'.$ref_table_config['links']['add_child']['url']."/". $ref_table;
			$child_link_label=$ref_table_config['links']['add_child']['label'];
			$child_link_title=$ref_table_config['links']['add_child']['title'];
			$add_child_link=True;
		}
	
		//delete link
		if(!empty(	$ref_table_config['links']['delete']) AND !empty($ref_table_config['links']['delete']['on_list'])  AND ($ref_table_config['links']['delete']['on_list']== True) ){
				
			$delete_link_label=$ref_table_config['links']['delete']['label'];
			$delete_link_title=$ref_table_config['links']['delete']['title'];
			$delete_link=True;
			
		
		}
	
		$i=1;
		
		/*
		 * Préparation de la liste à afficher sur base du contenu et  stucture de la table
		 */
		foreach ($data['list'] as $key => $value) {
			
			/*
			 * Ajout des liens(links) sur la liste
			 */
			
			$add_child_button="";
			$edit_button="";
			$view_button="";
			$action_button="";
			$arr_buttons=array();
				
			if($view_link){
	
					
				array_push($arr_buttons, array(
						'url'=> $view_link_url.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-folder"></i> '.$view_link_label,
						'title'=>$view_link_title
							
				)	);
	
				}
				
			if($edit_link){
	
	
				array_push($arr_buttons, array(
						'url'=> 'manage/edit_ref/' . $ref_table.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-pencil"></i> '.$edit_link_label,
						'title'=>$edit_link_title
							
				)	);
					
				
					
			}
				
			if($add_child_link){
	
				array_push($arr_buttons, array(
						'url'=> $child_link_url.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-plus"></i> '.$child_link_label,
						'title'=>$child_link_title
	
				)	);
	
			}
			
			if($delete_link){
					
					
				array_push($arr_buttons, array(
						'url'=> 'manage/delete_ref/' . $ref_table.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-trash"></i> '.$delete_link_label,
						'title'=>$delete_link_title
							
				)	);
					
				
			}
	
			$action_button=create_button_link_dropdown($arr_buttons);
			
			
			
			
			//$extra_values=$this->get_extra_fields($data ['list'] [$key] [$table_id]);
			//$data ['list'] [$key]['class_scope']=$extra_values['class_scope'];
			//$data ['list'] [$key]['class_intent']=$extra_values['class_intent'];
			//$data ['list'] [$key]['class_intent_relation']=$extra_values['class_intent_relation'];
			
			
			$data['list'][$key]['edit']=$action_button;
			
			
			
			
			
			$data ['list'] [$key] [$table_id] = $i + $page;
			unset($data ['list'] [$key] [$table_id]);
			
			
			/*
			 * Remplacement des clés externes par leurs correspondances
			 */
			foreach ($dropoboxes as $k => $v) {
				
			
				if($data ['list'] [$key] [$k]  ){
					
					if( isset($v[$data ['list'] [$key] [$k]]) ){
					if($k=='class_paper_id'){
					
						//Pour le nom du papier affichage des 15 premiers caractères
						$the_title=$v[$data ['list'] [$key] [$k]];
						$display=substr($the_title,0,15)."...";
						$data['list'][$key][$k]=anchor('manage/view_paper/'.$data ['list'] [$key] [$k],"<u><b>".$display."</b></u>","title='".$the_title."'");
											
					}else{
						
						$data ['list'] [$key] [$k]=$v[$data ['list'] [$key] [$k]];
					}
					}
				}
				else
				{
					if($ref_table_config['fields'][$k]['field_value']=="0_1"){
						$data ['list'] [$key] [$k]="No";
					}else{
						$data ['list'] [$key] [$k]="";
					
					}
					}
					
					
			}
	
			$i++;
		}
		
		/*
		 * Ajout de l'entête de la liste
		 */
	
		if(!empty($data['list'])){
			$array_header=$ref_table_config['header_list_fields'];
			unset($array_header[0]);
			//array_push($array_header,lng('Scope'));
			//array_push($array_header,lng('Intent'));
			//array_push($array_header,lng('Intent relation'));
			if(trim($data['list'][$key]['edit']) !=""){
				
				array_push($array_header,'');
			}
			$data['list_header']=$array_header;
			//array_unshift($data['list'],$array_header);
		}
		
		
		/*
		 * Création des boutons qui vont s'afficher en haut de la page (top_buttons)
		 */
		$data ['top_buttons']="";
	
		$data ['top_buttons'] .= get_top_button ( 'close', 'Close', 'home' );
	
		/*
		 * Titre de la page
		 */
		$data['page_title']=$ref_table_config['reference_title'].$sup_title;
	
	
		/*
		 * La vue qui va s'afficher
		 */
			$data['page']='liste_dt';
	
			/*
			 * Chargement de la vue avec les données préparés dans le controleur
			 */
			$this->load->view('body',$data);
	}
	
	
	
	
	/*
	 * Fonction  pour afficher la liste des papiers utilisant un Java script datatable
	 *
	 * Input: $paper_cat: indique la categorie à afficher
	 * 			$val : valeur de recherche si une recherche a été faite
	 * 			$page: la page à affiché : ulilisé par les lien de navigation
	 */
	
	public function list_paper($paper_cat='all',$val = "_", $page = 0){
		
		
		
		$ref_table="papers";
		
		/*
		 * Vérification si il y a une recherche faite
		 */
		$val = urldecode ( urldecode ( $val ) );
		$filter = array ();
		if (isset ( $_POST ['search_all'] )) {
			$filter = $this->input->post ();
			
			unset ( $filter ['search_all'] );
	
			$val = "_";
			if (isset ( $filter ['valeur'] ) and ! empty ( $filter ['valeur'] )) {
				$val = $filter ['valeur'];
				$val = urlencode ( urlencode ( $val ) );
			}
	
			/*
			 * mis à jours de l'url en ajoutant la valeur recherché dans le lien puis rechargement de l'url
			 */
			$url = "manage/list_paper/" . $paper_cat ."/". $val ."/0/";
	
			redirect ( $url );
		}
	
		
		/*
		 * Récupération de la configuration(structure) de la table à afficher
		 */
		$ref_table_config=$this->ref_table_config($ref_table);
		
		$table_id=$ref_table_config['table_id'];
		
		
		/*
		 * Appel du model pour récuperer la liste à afficher dans la Base de données
		 */
		$data=$this->DBConnection_mdl->get_papers($paper_cat,$ref_table_config,$val,$page);
		
		//for select dropboxes
		
		/*
		 * récupération des correspondances des clès externes 
		 */
		$dropoboxes=array();
		foreach ($ref_table_config['fields'] as $k => $v) {
			
				if(!empty($v['input_type']) AND $v['input_type']=='select' AND $v['on_list']=='show'){
					if($v['input_select_source']=='array'){
						$dropoboxes[$k]=$v['input_select_values'];
					}elseif($v['input_select_source']=='table'){
						$dropoboxes[$k]= $this->get_reference_select_values($v['input_select_values']);
					}
				}
			;
		}
		
		/*
		 * Vérification des liens (links) a afficher sur la liste
		 */
		$add_child_link=False;
		$edit_link=False;
		$view_link=False;
		$delete_link=False;
		
		//view link
		if(!empty(	$ref_table_config['links']['view']) AND !empty($ref_table_config['links']['view']['on_list'])  AND ($ref_table_config['links']['view']['on_list']== True) ){
			
			if(!empty($ref_table_config['links']['view']['url'])){
				$view_link_url=$ref_table_config['links']['view']['url'];
			}else{
				$view_link_url='manage/view_ref/' . $ref_table;
			}
				
			$view_link_label=$ref_table_config['links']['view']['label'];
			$view_link_title=$ref_table_config['links']['view']['title'];
			$view_link=True;
		}
		
		//edit link
		if(!empty(	$ref_table_config['links']['edit']) AND !empty($ref_table_config['links']['edit']['on_list'])  AND ($ref_table_config['links']['edit']['on_list']== True) ){
			
			$edit_link_label=$ref_table_config['links']['edit']['label'];
			$edit_link_title=$ref_table_config['links']['edit']['title'];
			$edit_link=True;
		}
		
		//delete link
		if(!empty(	$ref_table_config['links']['delete']) AND !empty($ref_table_config['links']['delete']['on_list'])  AND ($ref_table_config['links']['delete']['on_list']== True) ){
				
			$delete_link_label=$ref_table_config['links']['delete']['label'];
			$delete_link_title=$ref_table_config['links']['delete']['title'];
			$delete_link=True;
		}
		
		//addchild link
		if(!empty(	$ref_table_config['links']['add_child']['url']) AND !empty($ref_table_config['links']['add_child']['on_list'])  AND ($ref_table_config['links']['add_child']['on_list']== True) ){
			$child_link_url='manage/add_ref_child/'.$ref_table_config['links']['add_child']['url']."/". $ref_table;
			$child_link_label=$ref_table_config['links']['add_child']['label'];
			$child_link_title=$ref_table_config['links']['add_child']['title'];
			$add_child_link=True;
		}
		
		
		
		/*
		 * Préparation de la liste à afficher sur base du contenu et  stucture de la table
		 */
		
		$i=1;
		foreach ($data['list'] as $key => $value) { 
			
			/*
			 * Ajout des liens(links) sur la liste
			 */
			
			$add_child_button="";
			$edit_button="";
			$view_button="";
			$action_button="";
			$arr_buttons=array();
			
			$data['list'][$key]['bibtexKey']=anchor($view_link_url.'/'.$value [$table_id],"<u><b>".$data['list'][$key]['bibtexKey']."</b></u>");
			
			if($view_link){
				
			
			array_push($arr_buttons, array(
					'url'=> $view_link_url.'/'.$value [$table_id],
					'label'=>'<i class="fa fa-folder"></i> '.$view_link_label,
					'title'=>$view_link_title
					
			)	);
				
			
			}
			
			if($edit_link){
				
				
			array_push($arr_buttons, array(
						'url'=> 'manage/edit_ref/' . $ref_table.'/'.$value [$table_id],
						'label'=>'<i class="fa fa-pencil"></i> '.$edit_link_label,
						'title'=>$edit_link_title
							
				)	);
			
				
			}
			
		if($add_child_link){

			array_push($arr_buttons, array(
					'url'=> $child_link_url.'/'.$value [$table_id],
					'label'=>'<i class="fa fa-plus"></i> '.$child_link_label,
					'title'=>$child_link_title
						
			)	);
			
		}
		

		if($delete_link){
				
				
			array_push($arr_buttons, array(
					'url'=> 'manage/delete_ref/' . $ref_table.'/'.$value [$table_id],
					'label'=>'<i class="fa fa-trash"></i> '.$delete_link_label,
					'title'=>$delete_link_title
						
			)	);
				
		}
		
		$action_button=create_button_link_dropdown($arr_buttons);
		
			$data['list'][$key]['edit']=$action_button;
			$data ['list'] [$key] [$table_id] = $i + $page;

			
			/*
			 * Remplacement des clés externes par leurs correspondances
			 */
			foreach ($dropoboxes as $k => $v) {
				if($data ['list'] [$key] [$k]){
					if(isset($v[$data ['list'] [$key] [$k]])){
				$data ['list'] [$key] [$k]=$v[$data ['list'] [$key] [$k]];
					}
				}else{
				$data ['list'] [$key] [$k]="";
				}
			}
				
			$i++;
		}
		
		

		/*
		 * Ajout de l'entête de la liste
		 */
		if(!empty($data['list'])){
			$array_header=$ref_table_config['header_list_fields'];
			if(trim($data['list'][$key]['edit']) !=""){
				array_push($array_header,'');
			}
			array_unshift($data['list'],$array_header);
		}
	
		/*
		 * Création des boutons qui vont s'afficher en haut de la page (top_buttons)
		 */
		
		$data ['top_buttons']="";
		if($data['nombre']==0 AND $paper_cat== 'all'){
			//$data ['top_buttons'] .= get_top_button ( 'all', 'Add test papers', 'install/create_default_papers','test papers');
		}
		$data ['top_buttons'] .= get_top_button ( 'add', 'Add new', 'manage/add_ref/'.$ref_table );
		$data ['top_buttons'] .= get_top_button ( 'close', 'Close', 'home' );
		
	
		
		/*
		 * Titre de la page
		 */
		if($paper_cat== 'pending' OR $paper_cat== 'processed'  ){
			
			$data['page_title']=$ref_table_config['reference_title'].' - '.$paper_cat;
			
		}elseif($paper_cat== "assigned_me" ){
			$data['page_title']=$ref_table_config['reference_title'].' - Assigned to me';
			
		}elseif($paper_cat== "excluded" ){
			$data['page_title']=$ref_table_config['reference_title'].' - Excluded';
			
		}else{
		
			$data['page_title']=$ref_table_config['reference_title'];
		}
		
		
		
		$data ['nav_pre_link'] = 'manage/list_paper/' .$paper_cat.'/' . $val . '/';
		$data ['nav_page_position'] = 5;
		
		$data ['valeur']=($val=="_")?"":$val;
		
		if(!empty($ref_table_config['search_by'])){
		$data ['search_view'] = 'search_view';}
		
		
		/*
		 * La vue qui va s'afficher
		 */
		$data['page']='liste';
		
		/*
		 * Chargement de la vue avec les données préparés dans le controleur
		 */
		$this->load->view('body',$data);
	}
	
	
	
	/*
	 * Fonction pour recupérer les correspondances des clés externes
	 */
	private function get_reference_select_values($config,$start_with_empty=True,$get_leaf=False,$multiselect=False){
		
		$conf=explode(";", $config);
		//print_test($conf);
		$ref_table=$conf[0];
		$fields=$conf[1];
		$ref_table_config=$this->ref_table_config($ref_table);
		//for_array
		
		 if($get_leaf){
		 	
			while(!empty($ref_table_config['fields'][$fields]['input_type']) AND $ref_table_config['fields'][$fields]['input_type']=='select' AND $ref_table_config['fields'][$fields]['input_select_source']=='table' ){
				
				$config=$ref_table_config['fields'][$fields]['input_select_values'];
				
				$conf=explode(";", $config);
				//print_test($conf);
				$ref_table=$conf[0];
				$fields=$conf[1];
				$ref_table_config=$this->ref_table_config($ref_table);
				
				
				//echo "<h1>$fields</h1>";
			}
		}
		
		if($multiselect AND isset($ref_table_config['fields'][$fields]['input_select_source']) AND $ref_table_config['fields'][$fields]['input_select_source']=='array'){
			
			$result=array();
			
			
			
			$result=$ref_table_config['fields'][$fields]['input_select_values'];
			
		
			//print_test($result);
			//exit;
		}
		
		else{
			
			if($multiselect AND isset($ref_table_config['fields'][$fields]['input_select_source']) AND $ref_table_config['fields'][$fields]['input_select_source']=='table'){
					
				$config=$ref_table_config['fields'][$fields]['input_select_values'];
					
				$conf=explode(";", $config);
				//print_test($conf);
				$ref_table=$conf[0];
				$fields=$conf[1];
				$ref_table_config=$this->ref_table_config($ref_table);
					
					
					
			}
			
		$extra_condition="";
		
		if($ref_table_config['table_name']=='ref_values'){//It's a referennce table{
				
			$extra_condition = " AND  ref_category='".$ref_table."' ";
			
		}
		
		$res=$this->DBConnection_mdl->get_reference_select_values($ref_table_config,$fields,$extra_condition);
		
		$result=array();
		if($res AND $start_with_empty)
			$result['']="Select...";
		$_stable_config=$ref_table_config;	
		$_fields=$fields;	
		foreach ($res as $key => $value) {
			
			$ref_table_config = $_stable_config;
			$fields = $_fields;
			//print_test($ref_table_config);
			$result[$value['refId']]=$value['refDesc'];
			
			if($get_leaf OR 1==1){
				
				
				while(!empty($ref_table_config['fields'][$fields]['input_type']) AND $ref_table_config['fields'][$fields]['input_type']=='select' AND $ref_table_config['fields'][$fields]['input_select_source']=='table' ){
				//	echo "<h1>bbbb</h1>";
				//print_test($result);
				
					$config=$ref_table_config['fields'][$fields]['input_select_values'];
			
					$conf=explode(";", $config);
					
					$ref_table=$conf[0];
					$fields=$conf[1];
					$ref_table_config=$this->ref_table_config($ref_table);
					
					
					$res2=$this->manage_mdl->get_reference_value($ref_table_config['table_name'],$value['refDesc'],$fields,$ref_table_config['table_id']);
					
					$result[$value['refId']]= $res2;
				
					
				}
				
				
				 if(isset($ref_table_config['fields'][$fields]['input_select_source']) AND $ref_table_config['fields'][$fields]['input_select_source']=='array'){
				
				 $select_values=$ref_table_config['fields'][$fields]['input_select_values'];
				 	
				 $result[$value['refId']]= $select_values[$result[$value['refId']]];
				
				 }
				
				
				
			}
			
			
			
		}
		
		}
		//print_test($result);
		return $result;
		
		
	}
	
	
	
	/*
	 * spécialisation de la fonction add_ref_child lorsque le formulaire s'affiche en pop up
	 */
	public function add_ref_child_modal($ref_table_child="users",$child_field="user_usergroup",$ref_table_parent="usergroup",$parent_id=2, $data = "",$operation="new",$display_type="normal") {
		old_version();
		$this->add_ref_child($ref_table_child,$child_field,$ref_table_parent,$parent_id, $data,$operation,"modal");
	}
	
	
	
	/*
	* Fonction  pour afficher la page avec un formulaire d'ajout d'un élément avec une clé externe provenant de l'element  parent (exemple ajout d'un utilisateur à partir d'un groupe d'utilisateur)
	*
	* Input: 	$ref_table_child:le nom de la structure de l'élément enfant
	* 			$child_field: le nom de la clé externe dans la table enfant
	* 			$ref_table_parent:le nom de la structure de l'élement parent
	* 			$parent_id: l'id de l'élément parent
	* 			$data : informations sur l'élément enfant si la fonction est utilisé pour la mis à jour(modification)
	* 			$operation: type de l'opération ajout (new) ou modification(edit)
	* 			$display_type: indique comment le formulaire va être afficher normal ou modal(pop- up)
	*/
	public function add_ref_child($ref_table_child,$child_field,$ref_table_parent,$parent_id, $data = "",$operation="new",$display_type="normal") {
		old_version();
		/*
		 * chargement de la manière d'affichage du formulaire
		 */
		$this->session->set_userdata('submit_mode',$display_type);
		
		
		/*
		 * Récupération de la configuration(structure) de la table enfant
		 */
		$table_config_child=$this->ref_table_config($ref_table_child);
		$table_config_child['config_id']=$ref_table_child;
		$table_config_parent=$this->ref_table_config($ref_table_parent);
		
		$table_config_child['fields'][$child_field]['on_add']="hidden";
		$table_config_child['fields'][$child_field]['on_edit']="hidden";
		$table_config_child['fields'][$child_field]['input_type']="text";
		
		
		
		/*
		 * récupération des valeurs qui vont apparaitre dans les champs select
		 */
		
		//print_test($table_config_parent);
		foreach ($table_config_child['fields'] as $k => $v) {
	
			if(!empty($v['input_type']) AND $v['input_type']=='select'){
			//	print_test($v);
				if($v['input_select_source']=='table'){
					if(isset($table_config_child['fields'][$k]['multi-select']) AND $table_config_child['fields'][$k]['multi-select']=='Yes' )
					{
						$table_config_child['fields'][$k]['input_select_values']= $this->get_reference_select_values($v['input_select_values'],False,False,True);
					
					
					}else{
					$table_config_child['fields'][$k]['input_select_values']= $this->get_reference_select_values($v['input_select_values']);
					}
					
				}
				
			}
			
			
		}
		
		if($ref_table_child=="classification_intent_relation"){
				
			if($parent_id){
				$intents=$this->DBConnection_mdl->get_classification_intents($parent_id);
				//print_test($intents);
				$classification_intent=array();
				foreach ($intents as $key_intent => $value_intent) {
					if($key_intent==0)
						$classification_intent[""]=lng('Select')."...";
					$classification_intent[$value_intent['class_intent_id']]=$value_intent['ref_value'];
				}
					
					
					
				$table_config_child['fields']['class_intent_rel_intent1']['input_select_values']=$classification_intent;
				$table_config_child['fields']['class_intent_rel_intent2']['input_select_values']=$classification_intent;
					
			}
				
		}
		
		
		
		/*
		 * Prépartions des valeurs qui vont apparaitres dans le formulaire
		 */
		$data['content_item'][$child_field]=$parent_id;
		$data['table_config']=$table_config_child;	
		$data['operation_type']=$operation;	
		$data['operation_source']="parent";
		$data['child_field']=$child_field;
		$data['table_config_parent']=$ref_table_parent;
		$data['parent_id']=$parent_id;
		
		
		/*
		 * Titre de la page
		 */
		$data ['page_title'] = lng('Add '.$table_config_child['reference_title_min']);
		
		
		
		
		/*
		 * Création des boutons qui vont s'afficher en haut de la page (top_buttons)
		 */
		$data ['top_buttons'] = get_top_button ( 'back', 'Back', 'manage' );
	
		/*
		 * La vue qui va s'afficher
		 */
		$data ['page'] = 'frm_reference';
	

		/*
		 * Chargement de la vue avec les données préparés dans le controleur suivant le type d'affichage : (popup modal ou pas)
		 */
		if($display_type=='modal'){
			$this->load->view ( 'frm_reference_modal', $data );
		}else{
				$this->load->view ( 'body', $data );
		}
	}
	
	
	/*
	 * spécialisation de la fonction add_ref_drilldown lorsque le formulaire s'affiche en pop up
	 */
	public function add_ref_drilldown_modal($ref_table_child,$ref_table_parent,$parent_field,$parent_id, $data = "",$operation="new") {
		old_version();
		$this->add_ref_drilldown($ref_table_child,$ref_table_parent,$parent_field,$parent_id,$data,$operation,"modal");
	}
	
	
	
	
	/*
	 * Fonction  pour afficher la page avec un formulaire d'ajout d'un élément avec une clé externe provenant de l'élément enfant
	 *
	 * Input: 	$ref_table_child:le nom de la structure de l'élément enfant
	 * 			$ref_table_parent:le nom de la structure de l'élément parent
	 * 			$child_field: le champs qui va prendre la clé de l'element enfant
	 *			$parent_id: l'id de l'element parent
	 * 			$data : informations sur l'element enfant si la fonction est utilisé pour la mis à jour(modification)
	 * 			$operation: type de l'opération ajout (new) ou modification(edit)
	 * 			$display_type: indique comment le formulaire va être afficher normal ou modal(pop- up)
	 */
	public function add_ref_drilldown($ref_table_child,$ref_table_parent,$parent_field,$parent_id, $data = "",$operation="new",$display_type="normal") {
		old_version();
		/*
		 * chargement de la manière d'affichage du formulaire
		 */
		$this->session->set_userdata('submit_mode',$display_type);
		
		
		/*
		 * Récupération de la configuration(structure) de la table enfant
		 */
		$table_config_child=$this->ref_table_config($ref_table_child);
		$table_config_child['config_id']=$ref_table_child;
		
		
		/*
		 * Récupération de la configuration(structure) de la table parent
		 */
		$table_config_parent=$this->ref_table_config($ref_table_parent);
		$op_type=($operation=='new')?'on_add':'on_edit';
		
		
		
		/*
		 * récupération des valeurs qui vont apparaitre dans les champs select
		 */
		foreach ($table_config_child['fields'] as $k => $v) {
	
			if(!empty($v['input_type']) AND $v['input_type']=='select' AND ($v[$op_type]!='hidden' AND $v[$op_type]!='not_set') ){
				if($v['input_select_source']=='table' ){
					if(isset($table_config_child['fields'][$k]['multi-select']) AND $table_config_child['fields'][$k]['multi-select']=='Yes' )
					{
						$table_config_child['fields'][$k]['input_select_values']= $this->get_reference_select_values($v['input_select_values'],False,False,True);
					
					
					}else{
						$table_config_child['fields'][$k]['input_select_values']= $this->get_reference_select_values($v['input_select_values']);
					}
					
					}
			}
				
		}
		
		
		if($ref_table_child=="classification_intent_relation"){
		
			if($parent_id){
				$intents=$this->DBConnection_mdl->get_classification_intents($parent_id);
				$classification_intent=array();
				foreach ($intents as $key_intent => $value_intent) {
					if($key_intent==0)
						$classification_intent[""]=lng('Select')."...";
					$classification_intent[$value_intent['class_intent_id']]=$value_intent['ref_value'];
				}
					
				$table_config_child['fields']['class_intent_rel_intent1']['input_select_values']=$classification_intent;
				$table_config_child['fields']['class_intent_rel_intent2']['input_select_values']=$classification_intent;
					
			}
		
		}
		
		
		/*
		 * Prépartions des valeurs qui vont apparaitres dans le formulaire
		 */
		$data['parent_field']=$parent_field;
		$data['parent_id']=$parent_id;
		$data['parent_table']=$table_config_parent['table_name'];
		$data['table_config']=$table_config_child;		
		$data['operation_type']=$operation;		
		$data['operation_source']="drilldown";
		$data['parent_field']=$parent_field;
		$data['table_config_parent']=$ref_table_parent;
		$data['parent_id']=$parent_id;
		
		
		
		/*
		 * Titre de la page
		 */
		if($operation=='new')
		$data ['page_title'] = lng('Add '.$table_config_parent['fields'][$parent_field]['field_title']);
		else
		$data ['page_title'] = lng('Edit '.$table_config_parent['fields'][$parent_field]['field_title']);
		
		
		/*
		 * Création des boutons qui vont s'afficher en haut de la page (top_buttons)
		 */
		$data ['top_buttons'] = get_top_button ( 'back', 'Back', 'manage' );
	
		/*
		 * La vue qui va s'afficher
		 */
		$data ['page'] = 'frm_reference';
	
		
		/*
		 * Chargement de la vue avec les données préparés dans le controleur suivant le type d'affichage : (popup modal ou pas)
		 */
		if($display_type=='modal'){
			$this->load->view ( 'frm_reference_modal', $data );
		}else{
			$this->load->view ( 'body', $data );
		}
	}
	
	
	/*
	 * spécialisation de la fonction add_classification lorsque le formulaire s'affiche en pop up
	 */
	public function add_classification_modal($parent_id, $data = "",$operation="new") {

		$this->add_classification ( $parent_id, $data, $operation,'modal' );
	
	}
	
	
	
	/*
	 * Fonction  pour afficher la page avec un formulaire d'ajout d'une classification
	 *
	 * Input: $parent_id: l'id du papier à qui on va ajouter une classification
	 * 			$data : informations sur l'élément si la fonction est utilisé pour la mis à jour(modification)
	 * 			$operation: type de l'opération ajout (new) ou modification(edit)
	 * 			$display_type: indique comment le formulaire va être afficher normal ou modal(pop- up)
	 */
	
	public function add_classification($parent_id, $data = "",$operation="new",$display_type="normal") {
		/*
		 * chargement de la manière d'affichage du formulaire
		 */
		$this->session->set_userdata('submit_mode',$display_type);
		
		$ref_table_child= 'classification';
		$child_field= 'class_paper_id';
		$ref_table_parent= 'papers';
		
		/*
		 * Récupération de la configuration(structure) de la table classification
		 */
		$table_config_child=$this->ref_table_config($ref_table_child);
		$table_config_child['config_id']=$ref_table_child;
		
		//print_test($table_config_child);
		
		
		/*
		 * Récupération de la configuration(structure) de la table des papiers
		 */
		$table_config_parent=$this->ref_table_config($ref_table_parent);
	
		$table_config_child['fields'][$child_field]['on_add']="hidden";
		$table_config_child['fields'][$child_field]['on_edit']="hidden";
		$table_config_child['fields'][$child_field]['input_type']="text";
	
		
	
		
		/*
		 * récupération des valeurs qui vont apparaitre dans les champs select
		 */
		foreach ($table_config_child['fields'] as $k => $v) {
	
			if(!empty($v['input_type']) AND $v['input_type']=='select'){
				if($v['input_select_source']=='table'){
					if(isset($table_config_child['fields'][$k]['multi-select']) AND $table_config_child['fields'][$k]['multi-select']=='Yes' )
					{
						$table_config_child['fields'][$k]['input_select_values']= $this->get_reference_select_values($v['input_select_values'],False,False,True);
						
						
					}else{
						$table_config_child['fields'][$k]['input_select_values']= $this->get_reference_select_values($v['input_select_values']);
						
						
					}
				}
			}
				
		}
		/*
		 * Prépartions des valeurs qui vont apparaitres dans le formulaire
		 */
		
		$data['content_item'][$child_field]=$parent_id;
		$data['table_config']=$table_config_child;
		$data['operation_type']=$operation;
		$data['operation_source']="paper";
		$data['child_field']=$child_field;
		$data['table_config_parent']=$ref_table_parent;
		$data['parent_id']=$parent_id;
		
		

		/*
		 * Titre de la page
		 */
		
		$parrent_names=$this->get_reference_select_values($table_config_child['fields'][$child_field]['input_select_values']);
		
		if($operation=='edit'){
		$data ['page_title'] = lng('Edit  '.$table_config_child['reference_title_min']." for the ".$table_config_parent['reference_title_min'])." : ".$parrent_names[$parent_id];
		}else{
			$data ['page_title'] = lng('Add a '.$table_config_child['reference_title_min']." to the ".$table_config_parent['reference_title_min'])." : ".$parrent_names[$parent_id];
			
		}
		
		
		
		/*
		 * Création des boutons qui vont s'afficher en haut de la page (top_buttons)
		 */
		$data ['top_buttons'] = get_top_button ( 'back', 'Back', 'manage' );
		
		/*
		 * La vue qui va s'afficher
		 */
		$data ['page'] = 'frm_reference';
		
		
		/*
		 * Chargement de la vue avec les données préparés dans le controleur suivant le type d'affichage : (popup modal ou pas)
		 */
		if($display_type=='modal'){
		$this->load->view ( 'frm_reference_modal', $data );}else{
			$this->load->view ( 'body', $data );
		}
		
	}
	
	
	/*
	 * Fonction  pour afficher la page avec un formulaire pour assigner un papier à un utilisateur
	 *
	 * Input: 	$paper_id: l'id du papier
	 * 			$data : informations sur le papier si la fonction est utilisé pour la mis à jour(modification)
	 * 			$operation: type de l'opération ajout (new) ou modification(edit)
	 * 			
	 */
	
	public function new_assignation($paper_id, $data = "",$operation="new") {
	
		$ref_table_child= 'assignation';
		$child_field= 'assigned_paper_id';
		$ref_table_parent= 'papers';
	
		
		/*
		 * Récupération de la configuration(structure) de la table assignation
		 */
		$table_config_child=$this->ref_table_config($ref_table_child);
		$table_config_child['config_id']=$ref_table_child;
		
		/*
		 * Récupération de la configuration(structure) de la table des papiers
		 */
		$table_config_parent=$this->ref_table_config($ref_table_parent);
	
		$table_config_child['fields'][$child_field]['on_add']="hidden";
		$table_config_child['fields'][$child_field]['on_edit']="hidden";
		$table_config_child['fields'][$child_field]['input_type']="text";
	
		
	
		/*
		 * récupération des valeurs qui vont apparaitre dans les champs select
		 */
		foreach ($table_config_child['fields'] as $k => $v) {
	
			if(!empty($v['input_type']) AND $v['input_type']=='select'){
				if($v['input_select_source']=='table'){
					$table_config_child['fields'][$k]['input_select_values']= $this->get_reference_select_values($v['input_select_values']);
				}
			}
	
		}
		
		/*
		 * Prépartions des valeurs qui vont apparaitres dans le formulaire
		 */
		$data['content_item'][$child_field]=$paper_id;
		$data['table_config']=$table_config_child;
		$data['operation_type']=$operation;
		$data['operation_source']="assignation";
		$data['child_field']=$child_field;
		$data['table_config_parent']=$ref_table_parent;
		$data['parent_id']=$paper_id;
		
		
		$parrent_names=$this->get_reference_select_values($table_config_child['fields'][$child_field]['input_select_values']);
		
		/*
		 * Titre de la page
		 */
		if($operation=='edit'){
			$data ['page_title'] = lng('Edit the assignation to the '.$table_config_parent['reference_title_min']." : ".$parrent_names[$paper_id]);
		}else{
			$data ['page_title'] = 'Assign a user to the '.$table_config_parent['reference_title_min']." : ".$parrent_names[$paper_id];
	
		}
		
	
		
	
	
		/*
		 * Création des boutons qui vont s'afficher en haut de la page (top_buttons)
		 */
		$data ['top_buttons'] = get_top_button ( 'back', 'Back', 'manage' );
		
		
		/*
		 * La vue qui va s'afficher
		 */
		$data ['page'] = 'frm_reference';
		
		
		/*
		 * Chargement de la vue avec les données préparés dans le controleur 
		 */
		$this->load->view ( 'body', $data );
	}
	
	
	/*
	 * Fonction  pour afficher la page avec un formulaire pour l'exclusion d'un papier 
	 *
	 * Input: 	$paper_id: l'id du papier
	 * 			$data : informations sur le papier si la fonction est utilisé pour la mis à jour(modification)
	 * 			$operation: type de l'opération ajout (new) ou modification(edit)
	 *
	 */
	public function new_exclusion($paper_id, $data = "",$operation="new") {
	
		$ref_table_child= 'exclusion';
		$child_field= 'exclusion_paper_id';
		$ref_table_parent= 'papers';
	
		/*
		 * Récupération de la configuration(structure) de la table exclusion
		 */
		$table_config_child=$this->ref_table_config($ref_table_child);
		$table_config_child['config_id']=$ref_table_child;
		
		
		/*
		 * Récupération de la configuration(structure) de la table des papiers
		 */
		$table_config_parent=$this->ref_table_config($ref_table_parent);
	
		$table_config_child['fields'][$child_field]['on_add']="hidden";
		$table_config_child['fields'][$child_field]['on_edit']="hidden";
		$table_config_child['fields'][$child_field]['input_type']="text";
	
		
	
		
		foreach ($table_config_child['fields'] as $k => $v) {
	
			if(!empty($v['input_type']) AND $v['input_type']=='select'){
				if($v['input_select_source']=='table'){
					$table_config_child['fields'][$k]['input_select_values']= $this->get_reference_select_values($v['input_select_values']);
				}
			}
	
		}
		
		
		/*
		 * Prépartions des valeurs qui vont apparaitres dans le formulaire
		 */
		$data['content_item'][$child_field]=$paper_id;
		$data['table_config']=$table_config_child;
		$data['operation_type']=$operation;
		$data['operation_source']="exclusion";
		$data['child_field']=$child_field;
		$data['table_config_parent']=$ref_table_parent;
		$data['parent_id']=$paper_id;
		
		
		
		/*
		 * Titre de la page
		 */
		$parrent_names=$this->get_reference_select_values($table_config_child['fields'][$child_field]['input_select_values']);
		if($operation=='edit'){
			$data ['page_title'] = 'Edit Exclusion of the '.$table_config_parent['reference_title_min']." : ".$parrent_names[$paper_id];
		}else{
			$data ['page_title'] = 'Exclusion of the '.$table_config_parent['reference_title_min']." : ".$parrent_names[$paper_id];
				
		}
		
		
	
	
		/*
		 * Création des boutons qui vont s'afficher en haut de la page (top_buttons)
		 */
		$data ['top_buttons'] = get_top_button ( 'back', 'Back', 'manage' );
		
		
		/*
		 * La vue qui va s'afficher
		 */
		$data ['page'] = 'frm_reference';
	
	
		
		/*
		 * Chargement de la vue avec les données préparés dans le controleur suivant le type d'affichage : (popup modal ou pas)
		 */
		$this->load->view ( 'body', $data );
	}
	
	
	
	
	
	
/*
	 * Fonction  pour afficher la page avec un formulaire d'ajout ou modification d'un élément
	 *
	 * Input: 	$ref_table: nom de la structure de la table pour l'élément à afficher
	 * 			$data : informations sur l'élément si la fonction est utilisé pour la mis à jour(modification)
	 * 			$operation: type de l'opération ajout (new) ou modification(edit)
	 * 			$display_type: indique comment le formulaire va être afficher normal ou modal(pop- up)
	 */
	public function add_ref($ref_table, $data = "", $operation ='new',$display_type="normal") {
		old_version();
		if(admin_config($ref_table))
			$data['left_menu_admin']=True;
		/*
		 * charger la manière d'affichage du formulaire
		 */
		$this->session->set_userdata('submit_mode',$display_type);
		
		
		/*
		 * Récupération de la configuration(structure) de la table concerné
		 */
		$table_config=$this->ref_table_config($ref_table);
		//print_test($table_config);
		$table_config['config_id']=$ref_table;
		
		$type_op=$operation=='new'?"on_add":"on_edit";
		
		
		/*
		 * récupération des valeurs qui vont apparaitres dans les dropdown boxes
		 */
		foreach ($table_config['fields'] as $k => $v) {
				
			if(!empty($v['input_type']) AND $v['input_type']=='select'){
				if($v['input_select_source']=='table' AND ($v[$type_op]=='enabled' OR $v[$type_op]=='disabled')){
					
					
					if(isset($table_config['fields'][$k]['multi-select']) AND $table_config['fields'][$k]['multi-select']=='Yes' )
					{
						$table_config['fields'][$k]['input_select_values']= $this->get_reference_select_values($v['input_select_values'],False,False,True);
					
					
					}else{
						$table_config['fields'][$k]['input_select_values']= $this->get_reference_select_values($v['input_select_values'],True,False);
					
					}
					}
			}
			
		}
		
		
		/*
		 * Prépartions des valeurs qui vont apparaitres dans le formulaire 
		 */
		$title_append=$table_config['reference_title_min'];
		if($ref_table=="class_scheme"){// utilisé pour classification scheme
			$category="free";
			if(!empty($data['content_item']['scheme_category'])){
				$category=$data['content_item']['scheme_category'];
			}
			$data['content_item']['scheme_number_of_values']=1;
			$data['content_item']['scheme_order']=1;
			if($category=='static'){
				$data['content_item']['scheme_type']='text';
				$data['content_item']['scheme_size']='1';
				$table_config['fields']['scheme_type']['on_add']='hidden';
				$table_config['fields']['scheme_type']['on_edit']='hidden';
				$table_config['fields']['scheme_source_main_field']['on_add']='hidden';
				$table_config['fields']['scheme_source_main_field']['on_edit']='hidden';
				$table_config['fields']['scheme_source']['field_title']=lng('Source: Liste of values separated by ; ');
				$title_append="Static list field";
				
			}elseif($category=='dynamic'){
				$title_append="Dynamic list field";
				
			}else{
				$table_config['fields']['scheme_source']['on_add']='hidden';
				$table_config['fields']['scheme_source']['on_edit']='hidden';
				
				$table_config['fields']['scheme_source_main_field']['on_add']='hidden';
				$table_config['fields']['scheme_source_main_field']['on_edit']='hidden';
				$title_append="Simple field";
			}
			
		}elseif($ref_table=="classification_intent_relation"){
			
			/*
			 * Pour intent relation les intentions sont choisie dans la liste des intentions associés à la classification
			 */
			
			if(!empty($data['content_item']['class_intent_rel_classification_id'])){
			$intents=$this->DBConnection_mdl->get_classification_intents($data['content_item']['class_intent_rel_classification_id']);
			//print_test($intents);
			$classification_intent=array();
			foreach ($intents as $key_intent => $value_intent) {
				if($key_intent==0)
					$classification_intent[""]=lng('Select')."...";
				$classification_intent[$value_intent['class_intent_id']]=$value_intent['ref_value'];
			}
			
			
			
			$table_config['fields']['class_intent_rel_intent1']['input_select_values']=$classification_intent;
			$table_config['fields']['class_intent_rel_intent2']['input_select_values']=$classification_intent;
			
			}
			
		}
		
		
		
		$data['table_config']=$table_config;
		
		
		/*
		 * Titre de la page
		 */
		if ($operation == 'new') {
			$data ['page_title'] = lng('Add '.$title_append);
		} else {
			$data ['page_title'] = lng('Edit '.$title_append);
		}
		
		$data['operation_type']=$operation;
		
		/*
		 * Création des boutons qui vont s'afficher en haut de la page (top_buttons)
		 */
		$data ['top_buttons'] = get_top_button ( 'back', 'Back', 'manage' );
		
		
		/*
		 * La vue qui va s'afficher
		 */
		$data ['page'] = 'frm_reference';
		
	
		/*
		 * Chargement de la vue avec les données préparés dans le controleur suivant le type d'affichage : (popup modal ou pas)
		 */
	if($display_type=='modal'){
		$this->load->view ( 'frm_reference_modal', $data );}else{
			$this->load->view ( 'body', $data );
		}
	
	}
	
	
	/*
	 * Fonction  pour afficher la page avec un formulaire d'ajout pour classification scheme
	 *
	 * Input: $category: type  de champs
	 * 			$parent : parent
	 * 			
	 */
	public function add_class_scheme($category="free",$parent='main'){
		
		$data['content_item']['scheme_category']=$category;
		$data['content_item']['scheme_parent']=$parent;
		
		/*
		 * Appel de la fonction general d'affichage du formulaire d'ajout avec les info spécifiques au classification scheme
		 */
		$this->add_ref ('class_scheme', $data );
	}
	
	
	/*
	 * Affichage du formulaire pour modifier un élément
	 * $ref_table: le nom de la structure de  la table de l'élément 
	 * $ref_id: id de l'élement
	 * $display_type: indique comment le formulaire va être afficher normal ou modal(pop- up)
	 */
	public function edit_ref($ref_table,$ref_id,$display_type="normal") {
		old_version();
		/*
		 * Récupération de la configuration(structure) de la table de l'element
		 */
		$table_config=$this->ref_table_config($ref_table);
		
		
		/*
		 * Appel de la fonction du model pour récupérer la ligne à modifier
		 */
		$data ['content_item'] = $this->DBConnection_mdl->get_row_details($ref_table,$ref_id);
	
		/*
		 * Récuperation des valeurs pour les champs multi-select
		 */
		foreach ($table_config['fields'] as $key => $v) {
			if(!empty($v['input_type']) AND $v['input_type']=='select' AND $v['input_select_source']=='table'){
		
				if(!empty($v['multi-select']) AND $v['multi-select']=='Yes' )
				{
					
					$Tvalues_source=explode(';', $v['input_select_values']);
				
					$source_table_config=$this->ref_table_config($Tvalues_source[0]);
					$input_select_key_field=$v['input_select_key_field'];
					$input_child_field=$Tvalues_source[1];
		
					$extra_condition=" AND $input_select_key_field ='".$ref_id."'";
		
					$res_values=$this->DBConnection_mdl->get_reference_select_values($source_table_config,$input_child_field,$extra_condition);
					$data ['content_item'][$key]=array();
		
					foreach ($res_values as $key_r => $value_r) {
						array_push($data ['content_item'][$key], $value_r['refDesc']);
					}
					
				}
			}
				
				
				
				
		}
		
		/*
		 * Appel de la fonction d'affichage du formulaire
		 */
		$this->add_ref ( $ref_table, $data, 'edit' ,$display_type);
	}
	
	
	
	/*
	 * Fonction  pour afficher la page avec un formulaire un élément enfant
	 *
	 * Input: 	$ref_table:le nom de la structure de l'élément enfant
	 * 			$ref_table_parent:le nom de la structure de l'élément parent
	 * 			$parent_field: le champs qui va prendre la clé de l'element enfant
	 *			$parent_id: l'id de l'élément parent
	 *			$ref_id: id de l'element à modifier
	 * 			$display_type: indique comment le formulaire va être afficher normal ou modal(pop- up)
	 */
	public function edit_drilldown($ref_table,$ref_table_parent,$parent_field,$parent_id,$ref_id,$display_type="normal") {
		old_version();
		$this->session->set_userdata('submit_mode',$display_type);
		
		/*
		 * Récupération de la configuration(structure) de la table de l'element
		 */
		$table_config=$this->ref_table_config($ref_table);
	
		
		/*
		 * Appel de la fonction du model pour récupérer la ligne à modifier
		 */
		$data ['content_item'] = $this->DBConnection_mdl->get_row_details($ref_table,$ref_id);
		
		
		/*
		 * Récuperation des valeurs pour les champs multi-select
		 */
		foreach ($table_config['fields'] as $key => $v) {
			if(!empty($v['input_type']) AND $v['input_type']=='select' AND $v['input_select_source']=='table'){
		
				if(!empty($v['multi-select']) AND $v['multi-select']=='Yes' )
				{
					
					$Tvalues_source=explode(';', $v['input_select_values']);
					
					$source_table_config=$this->ref_table_config($Tvalues_source[0]);
					$input_select_key_field=$v['input_select_key_field'];
					$input_child_field=$Tvalues_source[1];
		
					$extra_condition=" AND $input_select_key_field ='".$ref_id."'";
		
					$res_values=$this->DBConnection_mdl->get_reference_select_values($source_table_config,$input_child_field,$extra_condition);
					$data ['content_item'][$key]=array();
		
					foreach ($res_values as $key_r => $value_r) {
						array_push($data ['content_item'][$key], $value_r['refDesc']);
					}
					
				}
			}
				
				
				
				
		}
		
		/*
		 * Appel de la fonction d'affichage du formulaire
		 */
		$this->add_ref_drilldown($ref_table,$ref_table_parent,$parent_field,$parent_id,$data,'edit',$display_type);
	}
	
	
	
	
	/*
	 * Affichage du formulaire pour modifier une exclusion d'un papier
	 * $ref_id: id de l'exclusion
	 */
	public function edit_exclusion($ref_id) {
		old_version();
		/*
		 * Appel de la fonction du model pour recupérer la ligne à modifier
		 */
		$data ['content_item'] = $this->DBConnection_mdl->get_row_details('exclusion',$ref_id);	
		
		
		/*
		 * Appel de la fonction d'affichage du formulaire
		 */
		$this->new_exclusion ( $data ['content_item']['exclusion_id'], $data, 'edit' );
	}
	
	
	

	/*
	 * Affichage du formulaire pour modifier une classification
	 * $ref_id: id de la classification
	 * $display_type: indique comment le formulaire va être afficher normal ou modal(pop- up)
	 */
	public function edit_classification($ref_id,$display_type="normal") {
		old_version();
		$this->session->set_userdata('submit_mode',$display_type);
		
		
		/*
		 * Récupération de la configuration(structure) de la table classification
		 */
		$ref_table="classification";
		$table_config=$this->ref_table_config($ref_table);
		
		//print_test($table_config);
		
		/*
		 * Appel de la fonction du model pour récupérer la ligne à modifier
		 */
		$data ['content_item'] = $this->DBConnection_mdl->get_row_details($ref_table,$ref_id);
		
		
		/*
		 * Récuperation des valeurs pour les champs multi-select
		 */
		foreach ($table_config['fields'] as $key => $v) {
		if(!empty($v['input_type']) AND $v['input_type']=='select' AND $v['input_select_source']=='table'){
				
					if(!empty($v['multi-select']) AND $v['multi-select']=='Yes' )
					{
						
						$Tvalues_source=explode(';', $v['input_select_values']);
						$source_table_config=$this->ref_table_config($Tvalues_source[0]);
						$input_select_key_field=$v['input_select_key_field'];
						$input_child_field=$Tvalues_source[1];
						
						$extra_condition=" AND $input_select_key_field ='".$ref_id."'";
						
						$res_values=$this->DBConnection_mdl->get_reference_select_values($source_table_config,$input_child_field,$extra_condition);
						$data ['content_item'][$key]=array();
						
						foreach ($res_values as $key_r => $value_r) {
							array_push($data ['content_item'][$key], $value_r['refDesc']);
						}
						
					}
			}
			
		}
		
		$extra_fields=array();
		$classification_data=$this->get_reference_detail('classification', $ref_id);
		
		foreach ($classification_data as $key => $value) {
			if($value['edit']==1)
				array_push($extra_fields, $value);
		}
		
		 $data['extra_fields']=$extra_fields;
		
	
		 /*
		  * Appel de la fonction d'affichage du formulaire
		  */
		$this->add_classification ( $data ['content_item']['class_paper_id'], $data, 'edit',$display_type );
	}
	
	
	/*
	 * Affichage du formulaire pour modifier une assignation d'un papier à un utilisateur
	 * $ref_id: id de l'assignation
	 */
	public function edit_assignation($ref_id) {
		old_version();
		/*
		 * Appel de la fonction du model pour recuperer la ligne à modifier
		 */
		$data ['content_item'] = $this->DBConnection_mdl->get_row_details("assignation",$ref_id);
		
		/*
		 * Appel de la fonction d'affichage du formulaire
		 */
		$this->new_assignation ( $data ['content_item']['assigned_id'], $data, 'edit' );
	}
	
	
	/*
	 * Fonction pour l'affichage d'un élément
	 * Input :	$ref_table : nom de la structure de l'element à afficher
	 * 			$ref_id : id de l'élément
	 * 			
	 */
	
	public function view_ref($ref_table,$ref_id,$allow_redirect="yes") {
	
		if(admin_config($ref_table))
			$data['left_menu_admin']=True;
		/*
		 * Rédirection vers la fonction spécialise pour l'affichage d'un papier si l'element à afficher est un papier
		 */
		if($ref_table=='papers' AND $allow_redirect=='yes'){
			
			redirect('manage/view_paper/'.$ref_id);
		}elseif($ref_table=='classification' AND $allow_redirect=='yes'){
			
			$paper_id=$this->DBConnection_mdl->get_classification_paper($ref_id);
			
			redirect('manage/view_paper/'.$paper_id);
		}
		
		if(!($this->session->userdata ( 'project_db' )) AND $ref_table=='config'){
		
			redirect('home');
		}
		
		/*
		 * Appel de la fonction  récupérer la ligne à afficher
		 */
		$item_data = $this->get_reference_detail($ref_table,$ref_id);
		$data['item_data']=$item_data;
		
		
		/*
		 * Récupération de la configuration(structure) de la table de l'élément
		 */
		$table_config=$this->ref_table_config($ref_table);
		
		
		
		/*
		 * Création des boutons qui vont s'afficher en haut de la page (top_buttons)
		 */
		$data ['top_buttons']="";
		if($ref_table=='class_scheme'){//for_classification_scheme
			$row_detail=$this->DBConnection_mdl->get_row_details ( $ref_table,$ref_id );
			
			$data ['top_buttons'] .= get_top_button ( 'add', 'Add free field', 'manage/add_class_scheme/free/'.$row_detail['scheme_label'],' Simple field' );
			$data ['top_buttons'] .= get_top_button ( 'add', 'Add static list', 'manage/add_class_scheme/static/'.$row_detail['scheme_label'],' Static list' );
			$data ['top_buttons'] .= get_top_button ( 'add', 'Add dynamic list', 'manage/add_class_scheme/dynamic/'.$row_detail['scheme_label'],' Dynamic list' );
		}
		
		if(!empty(	$table_config['links']['add_child']['url']) AND !empty($table_config['links']['add_child']['on_view'])  AND ($table_config['links']['add_child']['on_view']== True) ){
			
			$data ['top_buttons'] .= get_top_button ( 'all', $table_config['links']['add_child']['title'], 'manage/add_ref_child/'.$table_config['links']['add_child']['url']."/". $ref_table.'/'.$ref_id ,$table_config['links']['add_child']['label'] )." ";
			
		}
		
		if(!empty(	$table_config['links']['edit']) AND !empty($table_config['links']['edit']['on_view'])  AND ($table_config['links']['edit']['on_view']== True) ){
				
			$data ['top_buttons'] .= get_top_button ( 'edit', $table_config['links']['edit']['title'], 'manage/edit_ref/' . $ref_table.'/'.$ref_id )." ";
				
		}
		
		if(!empty(	$table_config['links']['delete']) AND !empty($table_config['links']['delete']['on_view'])  AND ($table_config['links']['delete']['on_view']== True) ){
			if($ref_table=='project'){
				$data ['top_buttons'] .= get_top_button ( 'delete', $table_config['links']['delete']['title'], 'install/remove_project_validation/' .$ref_id )." ";
					
			}else{
			$data ['top_buttons'] .= get_top_button ( 'delete', $table_config['links']['delete']['title'], 'manage/delete_ref/' . $ref_table.'/'.$ref_id )." ";		
			}
			}
		$data ['top_buttons'] .= get_top_button ( 'back', 'Back', 'manage' );
		
		
		/*
		 * Titre de la page
		 */
		$data ['page_title'] = lng($table_config['reference_title_min']);
		
		/*
		 * La vue qui va s'afficher
		 */
		$data ['page'] = 'view_reference';
		
		/*
		 * Chargement de la vue avec les données préparés dans le controleur suivant le type d'affichage : (popup modal ou pas)
		 */
		$this->load->view ( 'body', $data );
	}
	
	
	
	/*
	 * Fonction pour afficher une ligne d'une table avec remplacement des clès externes par leurs correspondances
	 */
	private function get_reference_detail($ref_table,$ref_id,$editable=True) {
	
		$table_config=$this->ref_table_config($ref_table);
		
	//	print_test($table_config); 
	
	
		$dropoboxes=array();
		
		foreach ($table_config['fields'] as $k => $v) {
	
			if(!empty($v['input_type']) AND $v['input_type']=='select' AND ! (isset($v['on_view']) AND $v['on_view']=='hidden')){
				if($v['input_select_source']=='array'){
					$dropoboxes[$k]=$v['input_select_values'];
				}elseif($v['input_select_source']=='yes_no' ){
					$dropoboxes[$k]= array( 
							0=>'No',
							1=>'Yes'
					);
				}elseif($v['input_select_source']=='table'){
					
					if( $ref_table=='papers' AND $k=='authors'){
					$this->db3 = $this->load->database(project_db(), TRUE);
					$sql="select P.paperauthor_id ,A.author_name from paperauthor P,author A where P.paperId=$ref_id AND P.authorId=A.author_id AND A.author_active=1 AND P.paperauthor_active=1 ";
					$res_author=$this->db3->query($sql)->result_array();
					$t_array=array(''=>'Select ...');
					//print_test($res_author);
					foreach ($res_author as $key_a => $value_a) {
						$t_array[$value_a['paperauthor_id']]=$value_a['author_name'];
					}
					//print_test($t_array);
					$dropoboxes[$k]=$t_array;
					
					}else{
						$dropoboxes[$k]= $this->get_reference_select_values($v['input_select_values']);
					}
				}
			}
			
		}
		
		//print_test($dropoboxes);
		//$detail_result = $this->manage_mdl->get_reference_details ( $table_config['table_name'],$table_config['table_id'],$ref_id );
		$detail_result = $this->DBConnection_mdl->get_row_details ( $ref_table,$ref_id );
	
		$content_item=$detail_result;
		
		//print_test($content_item);
		$item_data=array();
		
	
		foreach ($dropoboxes as $k => $v) {
			$content_item[$k.'_idd']=0;
			if(isset($content_item[$k])){
	
				if(isset($v[$content_item[$k]])){
						
					$content_item[$k.'_idd']=$content_item[$k];
	
					$content_item[$k]=$v[$content_item[$k]];
	
	
	
	
				}
			}
				else{
	
					$content_item[$k]="";
				}
		}
		
		//print_test($content_item);
	
		//print_test($content_item);
	
		foreach ($table_config['fields'] as $key => $value) {
			$array=array();
			//print_test($value);
	
			if(!(isset($value['on_view']) AND $value['on_view']=='hidden' )){
					
	
				$array['title']=$value['field_title'];
				$array['edit']=0;
				
				//$array['val']="";
	//cccc
				//for multi values
				if(isset($value['number_of_values']) AND ($value['number_of_values']=='*' OR $value['number_of_values']!='1') AND !empty($value['input_select_key_field'])){
					
					$Tvalues_source=explode(';', $value['input_select_values']);
					//echo "<h1>".$Tvalues_source[0]."<h1>";
					$source_table_config=$this->ref_table_config($Tvalues_source[0]);
					$input_select_key_field=$value['input_select_key_field'];
						
					$extra_condition=" AND $input_select_key_field ='".$ref_id."'";
						
					$res_values=$this->DBConnection_mdl->get_reference_select_values($source_table_config,$input_select_key_field,$extra_condition);
					
					// set add button
					$add_button = create_button_link('manage/add_ref_child/'.$Tvalues_source[0].'/'.$value['input_select_key_field'].'/'.$ref_table.'/'.$ref_id,'<i class="fa fa-plus"></i> Add',"btn-success",'Add ');
					
					if($ref_table=='classification'){//use modal for classification
					$modal_title="Add : ".$value['field_title'];
					$add_button='<a  class="btn btn-xs btn-info" data-toggle="modal" data-target="#relisformModal" data-operation_type="2"  data-modal_link="manage/add_ref_child_modal/'.$Tvalues_source[0].'/'.$value['input_select_key_field'].'/'.$ref_table.'/'.$ref_id.'"  data-modal_title="'.$modal_title.'" ><i class="fa fa-plus"></i>Add</a>';
					}
					$k_row=0;
					if(!(isset($value['multi-select']) AND $value['multi-select']=="Yes") AND $editable)	{
						$array['val2'][0]="<span> ".$add_button."</span>";
						
						$k_row=1;
						$array['edit']=1;
					}
						
					foreach ($res_values as $key_v => $value_v) {
	
	
						if(isset($dropoboxes[$key][$value_v['refId']]))
							$array['val2'][$k_row]=$dropoboxes[$key][$value_v['refId']];
	
	
						if(isset($value_v['refId'])  AND $value['input_select_source']=='table' AND isset($value['input_select_source_type']) ){
							//print_test($value);
							$Tconfig=explode(';', $value['input_select_values'])   ;
							//echo $content_item[$key.'_idd'];
							if($value_v['refId']!=0)
							{
								$edit_button = create_button_link('manage/edit_drilldown/'.$Tconfig[0].'/'.$ref_table.'/'.$key.'/'.$ref_id.'/'.$value_v['refId'],'<i class="fa fa-pencil"></i> Edit',"btn-info",'Edit ');
								
								$delete_button = create_button_link('manage/remove_drilldown/'.$value_v['refId'].'/'.$Tconfig[0].'/'.$ref_table.'/'.$ref_id.'/'.$key.'/no','<i class="fa fa-times"></i> Remove',"btn-danger",'Remove ','onlist','alert_ok');
								
								if($ref_table=='classification'){//use modal for classification
									$modal_title="Edit : ".$value['field_title'];
									$edit_button='<a  class="btn btn-xs btn-info" data-toggle="modal" data-target="#relisformModal" data-operation_type="2"  data-modal_link="manage/edit_drilldown/'.$Tconfig[0].'/'.$ref_table.'/'.$key.'/'.$ref_id.'/'.$value_v['refId'].'/modal"  data-modal_title="'.$modal_title.'" ><i class="fa fa-pencil"></i>Edit</a>';
									
									$delete_button = create_button_link('manage/remove_drilldown/'.$value_v['refId'].'/'.$Tconfig[0].'/'.$ref_table.'/'.$ref_id.'/'.$key.'/no/yes','<i class="fa fa-times"></i> Remove',"btn-danger",'Remove ','onlist','alert_ok');
									
								}
	
								
	
							if((isset($value['multi-select']) AND $value['multi-select']=="Yes") OR !$editable)	{
								$edit_button="";
								$delete_button="";
							}
								
								if(isset($value['input_select_source_type']) AND  $value['input_select_source_type']=='drill_down')
									$array['val2'][$k_row]="<span class='drilldown_link'>".anchor('manage/view_ref/'.$Tconfig[0].'/'.$value_v['refId'],$array['val2'][$k_row])."</span> <div class='navbar-right'>$edit_button $delete_button</div>";
								else 
									$array['val2'][$k_row].=" <div class='navbar-right'>$edit_button $delete_button</div>";
									
									
							}
						}
	
	
						$k_row++;
	
					}
				}else{
	
	
	
					$array['val']=isset($content_item[$key])?": ".$content_item[$key]:': ';
					$array['val2'][0]=isset($content_item[$key])?": ".$content_item[$key]:': ';
					
					// for images
					if(isset($value['input_type']) AND $value['input_type']=='image'){
						if(!empty($content_item[$key])){
							$img=$this->config->item('image_upload_path').$content_item[$key]."_thumb.jpg";
							$array['val2'][0]= img($img);
						}
						
					}
					
	
					///echo $content_item[$key.'_idd'];
					if(isset($content_item[$key.'_idd'])  AND $value['input_select_source']=='table' AND isset($value['input_select_source_type']) AND  $value['input_select_source_type']=='drill_down' ){
						//print_test($value);
						$Tconfig=explode(';', $value['input_select_values'])   ;
						//echo $content_item[$key.'_idd'];
						if($content_item[$key.'_idd']!=0)
						{
							if((isset($value['drill_down_type']) AND $value['drill_down_type'] == 'not_linked') OR !$editable)
							{
								$edit_button="";
								$delete_button="";
							}else{
							$edit_button = create_button_link('manage/edit_drilldown/'.$Tconfig[0].'/'.$ref_table.'/'.$key.'/'.$ref_id.'/'.$content_item[$key.'_idd'],'<i class="fa fa-pencil"></i> Edit',"btn-info",'Edit ');
	
							$delete_button = create_button_link('manage/remove_drilldown/'.$content_item[$key.'_idd'].'/'.$Tconfig[0].'/'.$ref_table.'/'.$ref_id.'/'.$key,'<i class="fa fa-times"></i> Remove',"btn-danger",'Remove ','onlist','alert_ok');
							}
							$array['val']="<span class='drilldown_link'>".anchor('manage/view_ref/'.$Tconfig[0].'/'.$content_item[$key.'_idd'],$array['val'])."</span> <div class='navbar-right'>$edit_button.$delete_button</div>";
							$array['val2'][0]="<span class='drilldown_link'>".anchor('manage/view_ref/'.$Tconfig[0].'/'.$content_item[$key.'_idd'],$array['val2'][0])."</span> <div class='navbar-right'>$edit_button.$delete_button</div>";
								
								
								
						}else{
							if((isset($value['drill_down_type']) AND $value['drill_down_type'] == 'not_linked') OR !$editable)
							{
							$add_button = "";
							}else{
							$array['edit']=1;
							$add_button = create_button_link('manage/add_ref_drilldown/'.$Tconfig[0].'/'.$ref_table.'/'.$key.'/'.$ref_id,'<i class="fa fa-plus"></i> Add',"btn-success",'Add ');
							
							if($ref_table=='classification'){//use modal for classification
								$modal_title="Add : ".$value['field_title'];
						$add_button='<a  class="btn btn-xs btn-success" data-toggle="modal" data-target="#relisformModal" data-operation_type="2"  data-modal_link="manage/add_ref_drilldown_modal/'.$Tconfig[0].'/'.$ref_table.'/'.$key.'/'.$ref_id.'"  data-modal_title="'.$modal_title.'" ><i class="fa fa-plus"></i>Add</a>';
							}
							
									
							}
							$array['val']="<span>: ".$add_button."</span>";
							$array['val2'][0]="<span>: ".$add_button."</span>";
						}
					}
				}
	
				array_push($item_data, $array);
				//array_push($item_data2, $array);
	
			}
		}
	//print_test($item_data);
		return $item_data;
	
	}
	
	
	
	
	
	
	
	/*
	 * Fonction spécialisé  pour l'affichage d'un papier
	 * Input:	$ref_id: id du papier
	 */
	public function view_paper($ref_id) {
		
		
		$ref_table="papers";
		
		/*
		 * Récupération de la configuration(structure) de la table des papiers
		 */
		$table_config=$this->ref_table_config($ref_table);
	
		
		/*
		 * Appel de la fonction  récupérer les informations sur le papier afficher
		 */
		$paper_data=$this->get_reference_detail('papers',$ref_id);
		
		
		/*
		 * Préparations des informations à afficher
		 */
		
		//venue
		$venue="";
		foreach ($paper_data as $key => $value) {
			if($value['title']=='Venue' AND !empty($value['val2'][0])){
				$venue=$value['val2'][0];
			}
		}
		
		//Authors
		$authors="";
		foreach ($paper_data as $key => $value) {
			
			if($value['title']=='Author' AND !empty($value['val2'])){
				
				if(count($value['val2']>1)){
					$authors='<table class="table table-hover" ><tr><td> '.$value['val2'][0].'</td></tr>';
					foreach ($value['val2'] as $k => $v) {
						if($k>0){
						$authors.="<tr><td> ".$v.'</td></tr>';
						}
					}
					
					$authors.="</table>";
				}else{
					
					$authors=" : ".$value['val2'][0];
				}
				
			}
		}
		
		$content_item = $this->DBConnection_mdl->get_row_details ( $ref_table,$ref_id );
		
		$paper_name=$content_item['bibtexKey']." - ".$content_item['title'];
		$paper_excluded=False;
		if($content_item['paper_excluded']=='1'){
			$paper_excluded=True;
		}
		
		$data['paper_excluded']=$paper_excluded;
		$item_data=array();
	

		$array['title']=$content_item['bibtexKey']." - ".$content_item['title'];
		
		if(!empty($content_item['doi'])){
			
			$array['title'].='<ul class="nav navbar-right panel_toolbox">
				<li>
					<a title="Go to the page" href="'.$content_item['doi'].'" target="_blank" >
				 		<img src="'.base_url().'cside/images/pdf.jpg"/>

					</a>
				</li>

				</ul>';
		}
			
		
		array_push($item_data, $array);
		
		$array['title']="<b>".lng('Abstract')." :</b> <br/><br/>".$content_item['preview'];
		array_push($item_data, $array);
		$array['title']="<b>".lng('Preview')." :</b> <br/><br/>".$content_item['bibtex'];
		array_push($item_data, $array);
		
		$array['title']="<b>".lng('Venue')." </b> ".$venue;
		//array_push($item_data, $array);
	
		$array['title']="<b>".lng('Authors')." </b> ".$authors;
		//array_push($item_data, $array);
	
			
	
		$data['item_data']=$item_data;
		
		
		
		
		
		
		
		
		
		
		/*
		 * Informations sur l'exclusion du papier si le papier est exclu
		 */
		
		$exclusion = $this->DBConnection_mdl->get_exclusion ($ref_id );
		
		$table_config3=$this->ref_table_config("exclusion");
		$dropoboxes=array();
		foreach ($table_config3['fields'] as $k => $v) {
		
			if(!empty($v['input_type']) AND $v['input_type']=='select' AND $k!='exclusion_paper_id'){
				if($v['input_select_source']=='array'){
					$dropoboxes[$k]=$v['input_select_values'];
				}elseif($v['input_select_source']=='table'){
					$dropoboxes[$k]= $this->get_reference_select_values($v['input_select_values']);
				}
			}
			;
		}
		
		
		$T_item_data_exclusion=array();
		$T_remove_exclusion_button =array();
		$item_data_exclusion=array();
		$delete_exclusion="";
		$edit_exclusion="";
		
		if (!empty($exclusion)) {
				
			//put values from reference tables
			foreach ($dropoboxes as $k => $v) {
				if(($exclusion[$k])){
						if(isset($v[$exclusion[$k]])){
					$exclusion[$k]=$v[$exclusion[$k]];}
				}
				else{
					$exclusion[$k]="";
				}
			}
				
			
			foreach ($table_config3['fields'] as $k_t => $v_t) {
					
				if(!(isset($v_t['on_view']) AND $v_t['on_view']=='hidden' ) AND  $k_t!='exclusion_paper_id'){
						
					$array['title']=$v_t['field_title'];
					$array['val']=isset($exclusion[$k_t])?": ".$exclusion[$k_t]:': ';
						
					array_push($item_data_exclusion, $array);
						
				}
			}
				
			$delete_exclusion= get_top_button ( 'delete', 'Cancel the exclusion', 'manage/remove_exclusion/'.$exclusion['exclusion_id']."/".$ref_id , 'Cancel the exclusion')." ";
				
			$edit_exclusion= get_top_button ( 'edit', 'Edit the exclusion', 'manage/edit_exclusion/'.$exclusion['exclusion_id'], 'Edit the exclusion')." ";
				
				
		}
		
		
		$data['data_exclusion']=$item_data_exclusion;
		$data['remove_exclusion_button']=$edit_exclusion.$delete_exclusion;
		
		
		
		
		
		
		/*
		 * Information sur la clstification du papier si le papiers est déjà classé
		 */
		
		$classification = $this->DBConnection_mdl->get_classifications ($ref_id );
		
		
		if(!empty($classification)){
			
			$classification_data=$this->get_reference_detail('classification', $classification[0]['class_id'],True);
			
			$data['classification_data']=$classification_data;
		
			$delete_button= get_top_button ( 'delete', 'Remove the classification', 'manage/remove_classification/'.$classification[0]['class_id']."/".$ref_id , 'Remove the classification')." ";
				
			$edit_button= get_top_button ( 'edit', 'Edit the classification', 'manage/edit_classification/'.$classification[0]['class_id'], 'Edit the classification')." ";
			
			$data['classification_button']=$edit_button." ".$delete_button;
		}else{
			if(!empty(	$table_config['links']['add_child']['url']) AND !empty($table_config['links']['add_child']['on_view'])  AND ($table_config['links']['add_child']['on_view']== True) ){
			
				//$data ['classification_button'] = '<li><a><button type="button" class="btn btn-success" data-toggle="modal" data-target="#relisformModal"  data-modal_link="manage/add_classification_modal/'.$ref_id.'"  data-operation_type="1" data-modal_title="Add classification  to : '.$paper_name.'" ><i class="fa fa-plus"></i> '.$table_config['links']['add_child']['label'] .' </button></a></li> ';
				$data ['classification_button'] =get_top_button ( 'add', 'Add classification', 'manage/add_classification/'.$ref_id, 'Add classification')." ";;
			
			}
		}
		
		
		
	
		/*
		 * Informations sur l'assignation du papier si le papier est assigné à un utilisateur
		 */
		
		$assignation = $this->DBConnection_mdl->get_assignations ($ref_id );
		
		
		$table_config3=$this->ref_table_config("assignation");		
		
		$dropoboxes=array();
		foreach ($table_config3['fields'] as $k => $v) {
		
			if(!empty($v['input_type']) AND $v['input_type']=='select' AND $k!='class_paper_id'){
				if($v['input_select_source']=='array'){
					$dropoboxes[$k]=$v['input_select_values'];
				}elseif($v['input_select_source']=='table'){
					$dropoboxes[$k]= $this->get_reference_select_values($v['input_select_values']);
				}
			}
			;
		}
		
		$T_item_data_assignation=array();
		$T_remove_assignation_button =array();
		foreach ($assignation as $k_class => $v_class) {
				
			//put values from reference tables
			foreach ($dropoboxes as $k => $v) {
				if(($assignation[$k_class][$k])){
						
					$assignation[$k_class][$k]=$v[$assignation[$k_class][$k]];
				}
				else{
					$assignation[$k_class][$k]="";
				}
			}
				
			
			$item_data_assignation=array();
			foreach ($table_config3['fields'] as $k_t => $v_t) {
					
				if(!(isset($v_t['on_view']) AND $v_t['on_view']=='hidden' ) AND  $k_t!='assigned_paper_id'){
						
					$array['title']=$v_t['field_title'];
					$array['val']=isset($v_class[$k_t])?": ".$assignation[$k_class][$k_t]:': ';
						
					array_push($item_data_assignation, $array);
						
				}
			}
				
			$T_item_data_assignation[$k_class]=$item_data_assignation;
				
							
			$delete_button= get_top_button ( 'delete', 'Remove the assignation', 'manage/remove_assignation/'.$v_class['assigned_id']."/".$ref_id , 'Remove the assignation')." ";
				
			$edit_button= get_top_button ( 'edit', 'Edit the assignation', 'manage/edit_assignation/'.$v_class['assigned_id'], 'Edit the assignation')." ";
				
			$T_remove_assignation_button[$k_class]=$edit_button.$delete_button;
				
		}
		
		$data['data_assignations']=$T_item_data_assignation;
		$data['remove_assignation_button']=$T_remove_assignation_button;
		
		
		
		
		
		
		
		/*
		 * Création des boutons qui vont s'afficher en haut de la page (top_buttons)
		 */
		$data ['top_buttons']="";
		
		$data ['add_assignation_buttons']=get_top_button ( 'all', "Assigne to a user", 'manage/new_assignation/'.$ref_id ,' Assigne to someone '," fa-plus ","  ",'btn-success' )." ";
		
		
		if(!$paper_excluded){
		
			$data ['top_buttons'].=get_top_button ( 'all', "Exclude the paper", 'manage/new_exclusion/'.$ref_id ,'Exclude'," fa-minus",'','btn-danger' )." ";
		
	
		if(!empty(	$table_config['links']['edit']) AND !empty($table_config['links']['edit']['on_view'])  AND ($table_config['links']['edit']['on_view']== True) ){
				
			$data ['top_buttons'] .= get_top_button ( 'edit', $table_config['links']['edit']['title'], 'manage/edit_ref/' . $ref_table.'/'.$ref_id )." ";
				
		}
	
		if(!empty(	$table_config['links']['delete']) AND !empty($table_config['links']['delete']['on_view'])  AND ($table_config['links']['delete']['on_view']== True) ){
		
			$data ['top_buttons'] .= get_top_button ( 'delete', $table_config['links']['delete']['title'], 'manage/delete_ref/' . $ref_table.'/'.$ref_id )." ";
		
		}
	
		}
		
		
		$data ['top_buttons'] .= get_top_button ( 'back', 'Back', 'manage' );
	
		
		
		/*
		 * Titre de la page
		 */
		$data ['page_title'] = lng($table_config['reference_title_min']);
		
		if($paper_excluded){
			$data ['page_title'] = lng("Paper excluded");
		}
	
	
		/*
		 * La vue qui va s'afficher
		 */
		$data ['page'] = 'view_paper';
	
		
		/*
		 * Chargement de la vue avec les données préparés dans le controleur suivant le type d'affichage : (popup modal ou pas)
		 */
		$this->load->view ( 'body', $data );
	}
	
	
	
	/*
	 * Fonction pour enregistrer apres la soumissiond d'un formulaire
	 */
	public function save_ref() {
		
		/*
		 * Récuperation des valeurs soumis dans le formulaire
		 */
		$post_arr = $this->input->post ();
		//print_test($post_arr); exit;
		
		/*
		 * Récupération de la configuration (structure ) de la table qui est concerné
		 */
		$table_config=$this->ref_table_config($post_arr['table_config']);
		
		if($post_arr['operation_type']=='new'){
			$var_check="on_add";
		}else{
			$var_check="on_edit";
		}
		
		$operation_type=$post_arr['operation_type'];
		
		if($post_arr['table_config']=='class_scheme'){
			
			if(!empty($post_arr['scheme_title'])){
				$post_arr['scheme_label']=Slug($post_arr['scheme_title']);
			}
		}
		
		
		
		/*
		 * Validation du formulaire: vérification si les valeurs sont rémplis correctement 
		 */
		
		$this->load->library ( 'form_validation' );
		$other_check = true;
		$data ['err_msg'] = '';//for users
		
		$images_to_upload=array();
		$multi_select_values=array();
		
		foreach ($table_config['fields'] as $key => $value) {
			$validation="trim";
			if($value[$var_check]=='enabled'){
				
				if(!empty($value['mandatory']) AND trim($value['mandatory'])=="mandatory"  ){
					if((isset($value['multi-select']) AND isset($value['multi-select'])=='Yes')){
						if(empty($post_arr[$key])){
							
							$other_check = false;
							$data ['err_msg'] .= " The Field '".$value['field_title']."' is required<br/>";
						}
					}else{
						$validation.="|required";
					}
					
				}
				
				$this->form_validation->set_rules ( $key,'"'. $value['field_title'].'"', $validation );
				
			}
			
			if((isset($value['multi-select']) AND isset($value['multi-select'])=='Yes')){//multi- select
				if(!empty($post_arr[$key])){
					$multi_select_values[$key]['values']=$post_arr[$key];
					$multi_select_values[$key]['config']=$value;
				}else{
					$multi_select_values[$key]['values']=array();
					$multi_select_values[$key]['config']=$value;
				}
				
			}
			
			
			if(isset($value['input_type']) AND $value['input_type']=='image' AND !empty($_FILES [$key] ['name'])){
				
				$post_arr[$key]="";
				if(empty($_FILES [$key] ['tmp_name'])){
					$other_check = false;
					$data ['err_msg'] .= 'Problem with uploading image <br/>';
					
				}elseif( ! file_exists ( $_FILES [$key] ['tmp_name'] )){
					$other_check = false;
					$data ['err_msg'] .= 'Problem with uploading image <br/>';
				}elseif(!empty($_FILES [$key] ['name'])){
					$images_to_upload[$key]['info']=$_FILES [$key];
					
					
					$file_extension = "";
					switch ($_FILES [$key] ['type']) {
						case 'image/jpeg' :
							$file_extension = '.jpg';
							break;
								
						case 'image/png' :
							$file_extension = '.PNG';
							break;
								
						case 'image/gif' :
							$file_extension = '.gif';
							break;
								
						
					}
					
					$image_upload_path=$this->config->item('image_upload_path');
					if($post_arr['table_config']=='author'){
					$file_name = substr ( Slug ( $post_arr ['author_name'] ), 0, 20 ) . time ();
					}elseif($post_arr['table_config']=='users'){
					$file_name = substr ( Slug ( $post_arr ['user_name'] ), 0, 20 ) . time ();
					}else{
					$file_name =time ();
					}
					$config ['file_name'] = $file_name . $file_extension;
					$config ['upload_path'] = FCPATH . $image_upload_path;
					$config ['allowed_types'] = 'gif|jpg|png';
					$config ['overwrite'] = TRUE;
					$config ['remove_spaces'] = TRUE;
					$config ['max_filename'] = '0';
					$config ['max_size'] = 0; // use the system limit... see php.ini config in regards
					$config ['max_width'] = '0'; // should be 360 at destination
					$config ['max_height'] = '0'; // should be 300 at destination
					
					$images_to_upload[$key]['config']=$config;
					$images_to_upload[$key]['picture_name']=$file_name;
					
					$post_arr[$key]= $file_name;
					
					
					
					
					
					
				}
				
			}
				
		}
		
		
	
		if($post_arr['table_config']=='users'){
			
			$this->form_validation->set_rules('user_username', $table_config['fields']['user_username']['field_title'], 'trim|required|min_length[2]|max_length[12]');
			
			if(!(empty($post_arr['user_password']) AND $post_arr['operation_type']=='edit')){
				$this->form_validation->set_rules('user_password', $table_config['fields']['user_password']['field_title'], 'trim|required|matches[user_password_val]');
				$this->form_validation->set_rules('user_password_val', $table_config['fields']['user_password']['field_title'].' Confirmation', 'trim|required');
			
			}
			
			if(!empty($post_arr['user_mail'])){
			$this->form_validation->set_rules('user_mail', 'Email', 'trim|valid_email');
			
			}
			
			///vérify if the username is unique
			
			
			
			if ( !empty( $post_arr ['user_username']) AND ( $post_arr['operation_type']=='new')AND !$this->bm_lib->login_available($post_arr ['user_username'])) {
				
				$data ['err_msg'] .= 'Username already used <br/>';
				$other_check = FALSE;
			}
			
		}
		
		
			
		$operation_source=$post_arr ['operation_source'];
		$parent_id=$post_arr ['parent_id'];
		
		
		if(isset($post_arr ['table_config_parent']))
			$table_config_parent=$post_arr ['table_config_parent'];
		
		
		if ($this->form_validation->run () == FALSE OR  !$other_check) {

			/*
			 * Si la validation du formulaire n'est pas concluante , retour au formulaire de saisie
			 */
				
			$data ['content_item'] = $post_arr;
			
			
			if($post_arr ['operation_source']=='parent' )
			{
				
				if($this->session->userdata('submit_mode') AND $this->session->userdata('submit_mode') =='modal' ){
					$this->add_ref_child_modal ($post_arr ['table_config'], $post_arr ['child_field'],$post_arr ['table_config_parent'],$post_arr ['parent_id'],$data,$post_arr['operation_type'] );
						
				}else{
					
					$this->add_ref_child ($post_arr ['table_config'], $post_arr ['child_field'],$post_arr ['table_config_parent'],$post_arr ['parent_id'],$data,$post_arr['operation_type'] );
						
				}
				
			}elseif($post_arr ['operation_source']=='drilldown' )
			
			{
				
				
				if($this->session->userdata('submit_mode') AND $this->session->userdata('submit_mode') =='modal' ){
					
					$this->add_ref_drilldown_modal ($post_arr ['table_config'], $post_arr ['table_config_parent'], $post_arr ['parent_field'],$post_arr ['parent_id'],$data,$post_arr['operation_type'] );}
					else{
						
						$this->add_ref_drilldown ($post_arr ['table_config'], $post_arr ['table_config_parent'], $post_arr ['parent_field'],$post_arr ['parent_id'],$data,$post_arr['operation_type'] );
					}
				
				
			}elseif($post_arr ['operation_source']=='paper' )
			{
				if($this->session->userdata('submit_mode') AND $this->session->userdata('submit_mode') =='modal' ){
					$this->add_classification_modal ($parent_id,$data,$post_arr['operation_type'] );}
				else{
					$this->add_classification ($parent_id,$data,$post_arr['operation_type'] );
				}
			
			}elseif($post_arr ['operation_source']=='exclusion' )
			{
				$this->new_exclusion ($parent_id,$data,$post_arr['operation_type'] );
			}else{
				if($this->session->userdata('submit_mode') AND $this->session->userdata('submit_mode') =='modal' ){
					$this->add_ref ($post_arr ['table_config'],$data,$post_arr['operation_type'],'modal' );
				}else{
				$this->add_ref ($post_arr ['table_config'],$data,$post_arr['operation_type'] );
				}
			}
		} else {
		
		/*
		 * Si la validation du formulaire est concluante, proceder à l'enregistrement
		 */	
			
			
		/*
		 * Si le formulaire contient des images commencer par les redimentionner puis les sauvergarder
		 */	
		$image_upload_result=TRUE;
		if(!empty($images_to_upload)){
			foreach ($images_to_upload as $k_img => $v_img) {
				
				 $this->load->library ( 'upload', $v_img['config'] );
				 if (! $this->upload->do_upload ( $k_img )) {
				 	$image_upload_result=False;
					 $error = array (
					 'error' => $this->upload->display_errors ()
					 );
					 $data ['err_msg'] = $error ['error'];
					 	
					 
					//echo $data ['err_msg'] ; 
				 	
				 }else{
				 	// resizing images 
				 	
				 	// image size after resizing
				 	$thumb_size=$this->config->item('image_thumb_size');
                    $medium_size=$this->config->item('image_medium_size');
                    $big_size=$this->config->item('image_big_size');
                    $image_upload_path=$this->config->item('image_upload_path');
				 	
                    $image_data=$this->upload->data();
                    
                    $config_thumb=array(
                    		'source_image'=>$image_data['full_path'],
                    		'new_image' => FCPATH.$image_upload_path.$v_img['picture_name']."_thumb.jpg",
                    		'maintain_ratio' => true,
                    		'width' => $thumb_size,
                    		'height' => $thumb_size
                    );
                    
                    $config_medium=array(
                    		'source_image'=>$image_data['full_path'],
                    		'new_image' => FCPATH.$image_upload_path.$v_img['picture_name']."_med.jpg",
                    		'maintain_ration' => true,
                    		'width' => $medium_size,
                    		'height' => $medium_size
                    );
                    
                    
                    $config_big=array(
                    		'source_image'=>$image_data['full_path'],
                    		'new_image' => FCPATH.$image_upload_path.$v_img['picture_name']."_big.jpg",
                    		'maintain_ration' => true,
                    		'width' => $big_size,
                    		'height' =>$big_size
                    );
                    
                    
                    $this->load->library('image_lib');
                    $this->image_lib->initialize($config_thumb);
                    print_test($config_thumb);
                    	
                    if ( ! $this->image_lib->resize())
                    {
                    	die($this->image_lib->display_errors());
                    }
                    
                    $this->image_lib->clear();
                    	
                    $this->image_lib->initialize($config_medium);
                    	
                    if ( ! $this->image_lib->resize())
                    {
                    	die($this->image_lib->display_errors());
                    }
                    
                    
                    $this->image_lib->clear();
                    	
                    $this->image_lib->initialize($config_big);
                    	
                    if ( ! $this->image_lib->resize())
                    {
                    	die($this->image_lib->display_errors());
                    }
				 }
				 
				
				
			}
		}
			
			
			if(! $image_upload_result){
				
				/*
				 * Si le chagement d'image de marche pas retour au formulaire d'ajout
				 */
				$data ['content_item'] = $post_arr;
					
					
				if($post_arr ['operation_source']=='parent' )
				{
					$this->add_ref_child ($post_arr ['table_config'], $post_arr ['child_field'],$post_arr ['table_config_parent'],$post_arr ['parent_id'],$data,$post_arr['operation_type'] );
				}elseif($post_arr ['operation_source']=='drilldown' )
				{
					$this->add_ref_drilldown ($post_arr ['table_config'], $post_arr ['table_config_parent'], $post_arr ['parent_field'],$post_arr ['parent_id'],$data,$post_arr['operation_type'] );
				
				
				
				
				}elseif($post_arr ['operation_source']=='paper' )
				{
					$this->add_classification ($parent_id,$data,$post_arr['operation_type'] );
				}elseif($post_arr ['operation_source']=='exclusion' )
				{
					$this->new_exclusion ($parent_id,$data,$post_arr['operation_type'] );
				}else{
				
					$this->add_ref ($post_arr ['table_config'],$data,$post_arr['operation_type'] );
				}
				
			}else{
				
				
			/*
			 * Pour les utilisateur, si lors de la modification on a pas saisie un mot de passe ou ajouter une nouvelle photo , on garde les anciennes valeurs
			 */
			if($post_arr['table_config']=='users'){
				
				if(!(empty($post_arr['user_password']) AND $post_arr['operation_type']=='edit')){
					
					$post_arr['user_password']=md5($post_arr['user_password']);
					
				}else{
					if(!empty($post_arr['user_password_old']))
					{
						$post_arr['user_password']=$post_arr['user_password_old'];
					}else{
					unset($post_arr['user_password']);
					}
				}
				
				if($post_arr['operation_type']=='edit' AND empty($post_arr['user_picture'])){
					
					$post_arr['user_picture']=!empty($post_arr['user_picture_old'])?$post_arr['user_picture_old']:"";
				}
				
				unset($post_arr['user_password_val']);
			}
			
			
			
			
			
			/*
			 * Préparation des données avant l'appel de la foction qui va suvegarder les valeurs dans la BD
			 */
			
			$drill_table_config_parent=isset($post_arr ['table_config_parent'])?$post_arr ['table_config_parent']:"";
			$drill_parent_field=isset($post_arr ['parent_field'])?$post_arr ['parent_field']:"";
			$drill_parent_id=isset($post_arr ['parent_id'])?$post_arr ['parent_id']:0;
			$drill_parent_table=isset($post_arr ['parent_table'])?$post_arr ['parent_table']:"";
			
			
					
			unset ( $post_arr ['operation_source'] );
			unset ( $post_arr ['child_field'] );
			unset ( $post_arr ['table_config_parent'] );
			unset ( $post_arr ['parent_id'] );
			unset ( $post_arr ['parent_field'] );
			unset ( $post_arr ['parent_table'] );
			
			$post_arr['table_name']=$table_config['table_name'];
			$post_arr['table_id']=$table_config['table_id'];
			
			
			
			/*
			 * Appel de la fonction dna le modèle pour suvegarder les données dans la BD
			 */			
			$saved_res=$this->DBConnection_mdl->save_reference ( $post_arr,'get_id' );
			
			if ($saved_res) {		
				
				echo ("Enregistrement reussit");
				set_top_msg("Success");
				
				if($operation_source=='exclusion'){
					/*
					 * Pour l'exclusion d'un papier après la sauvegarde des info sur l'exclusion on appelle une fonction pour mettre à jour le papier
					 */
					$res=$this->DBConnection_mdl->exclude_paper ( $parent_id );
					
				}
				
				
				/*
				 * Pour l'enregistrement d'un élément enfant on met à jour la clef externe dans élément parent
				 */
				if($operation_source=='drilldown' AND $operation_type=='new'){
				
				$table_config_parent=$this->ref_table_config($drill_table_config_parent);
				
				$parent_table_id=$table_config_parent ['table_id'];
					
				$array_drill=array('operation_type'=>'edit',
						'table_config'=> $drill_table_config_parent,
						'table_name'=> $table_config_parent ['table_name'],
						'table_id'=> $parent_table_id,
						$parent_table_id=> $drill_parent_id,
						$drill_parent_field=> $saved_res,
				);
				
				$res_drill=	$this->manage_mdl->save_reference ( $array_drill );
				}
				
				
				
				/*
				 * Si le formulaire contient des champs multi-select : on appel une fonction pour sauvegarder ces valeurs dans leur table
				 */
				if(!empty($multi_select_values)){
				
					
					$this->save_multi_select($multi_select_values,$saved_res);
						
						
				}
				
				
			$message_modal="modal_relis_outputmessage_correct";	
				
			} else {
				// erreur d'enregistrement
				$message_modal="modal_relis_outputmessage_error";
			}
			
			
			
			if($this->session->userdata('submit_mode') AND $this->session->userdata('submit_mode') =='modal' ){
				/*
				 * Si le formulaire sauvegardé est affiché en popup on retourne le message d'erreur
				 */
				echo $message_modal;
				
			}else{
				/*
				 * Si le formulaire sauvegardé n'est affiché en popup on redirige vers la page d'affichage suivant l'élément enregistre
				 */
				if($operation_source=='paper' OR $operation_source=='assignation'  OR $operation_source=='exclusion'){
					
					redirect ( 'manage/view_paper/' .$parent_id  );
				}elseif($operation_source=='drilldown'){
					redirect ( 'manage/view_ref/'.$drill_table_config_parent.'/'.$drill_parent_id);
					
				}elseif($operation_source=='parent' AND !empty($table_config_parent) AND !empty($parent_id)){
					redirect ( 'manage/view_ref/'.$table_config_parent.'/'.$parent_id);
					
				}else{
					
					
					if($table_config['table_name']=='paper'){
						//redirect ( 'manage/list_paper');
						redirect ( 'manage/view_paper/'.$saved_res);
					}else{
						redirect ( 'manage/liste_ref/' . $post_arr ['table_config'] );
					}
					
				}
			
			}
			
		}
		}
	}
	
	
	/*
	 * Fonction pour enregistrer les valeurs dans les champs multi-select
	 */
	private function save_multi_select($multi_select_values,$parent_id){
		
		
		foreach ($multi_select_values as $k => $v) {
			
			$new_values=$v['values'];
		
			$Tvalues_source=explode(';', $v['config']['input_select_values']);
		
			
			$child_tab_config=$Tvalues_source[0];
			$input_select_key_field=$v['config']['input_select_key_field'];
			$input_child_field=$Tvalues_source[1];
			
			$source_table_config=$this->ref_table_config($child_tab_config);
			$extra_condition=" AND $input_select_key_field ='".$parent_id."'";
		
			$res_values=$this->DBConnection_mdl->get_reference_select_values($source_table_config,$input_child_field,$extra_condition);
			//print_test($res_values);
			$old_values=array();
		
			$tobe_removed=array();
			foreach ($res_values as $key_r => $value_r) {
				array_push($old_values, $value_r['refDesc']);
				if(! in_array($value_r['refDesc'], $new_values)){
					array_push($tobe_removed, $value_r['refId']);
				}
			}
		
			//print_test($new_values);
			//print_test($old_values);
		
			//elements to be added
			$tobe_added=array_diff($new_values,$old_values);
			//print_test($tobe_added);
		
			
		
			//$tobe_removed=array_diff($old_values,$new_values);
			//print_test($tobe_removed);
			
			//remove deleted values
			foreach ($tobe_removed as $k_rem => $v_rem) {
				
				$res=$this->DBConnection_mdl->remove_element($v_rem,$child_tab_config);
			}
			
			//adding new values
			foreach ($tobe_added as $k_add => $v_add) {
				$array_add=array();
				$array_add['operation_type']='new';
				$array_add['table_config']=$child_tab_config;
				$array_add[$input_select_key_field]=$parent_id;
				$array_add[$input_child_field]=$v_add;
				
				$saved_res=$this->DBConnection_mdl->save_reference ( $array_add,'get_id' );
				
				//print_test($array_add);
			}
			
			
		
		}
	}
	
	
	/*
	 * Récuperation de la structure de la table
	 */
	private function ref_table_config($_table)
	{
		//moved to library
		
		return $this->table_ref_lib->ref_table_config($_table);
	}
	
	public function remove_classification ($id,$paper_id){
		//$res=$this->manage_mdl->remove_element($id,'classification','class_id','class_active');
		$res=$this->DBConnection_mdl->remove_element($id,'classification');
		
		redirect ( 'manage/view_paper/' .$paper_id  );
	}
	
	public function remove_assignation ($id,$paper_id){
		//$res=$this->manage_mdl->remove_element($id,'assigned','assigned_id','assigned_active');
		$res=$this->DBConnection_mdl->remove_element($id,'assignation');
		redirect ( 'manage/view_paper/' .$paper_id  );
	}
	
	public function remove_exclusion ($id,$paper_id){
		//$res=$this->manage_mdl->remove_element($id,'exclusion','exclusion_id','exclusion_active');
		$res=$this->DBConnection_mdl->remove_element($id,'exclusion');
		//$res1=$this->manage_mdl->include_paper($paper_id);
		$res1=$this->DBConnection_mdl->include_paper($paper_id);
	
		redirect ( 'manage/view_paper/' .$paper_id  );
	}
	
	public function remove_drilldown ($child_id,$table_config_child,$table_config_parent,$parent_id,$parent_field,$update_parrent='yes',$modal='no'){
		
		
		$this->delete_ref($table_config_child, $child_id,FALSE);
		
		if($update_parrent=='yes'){
		$parent_config=$this->ref_table_config($table_config_parent);
		
		$array_drill=array('operation_type'=>'edit',
				'table_config'=> $table_config_parent,
				'table_name'=> $parent_config ['table_name'],
				'table_id'=> $parent_config ['table_id'],
				$parent_config ['table_id']=> $parent_id,
				$parent_field=> 0
		);
		
		$res_drill=	$this->manage_mdl->save_reference ( $array_drill );
		
		}
		if($modal=='yes')
		{
			redirect ( 'manage/edit_classification/'.$parent_id  );
		}else{
			redirect ( 'manage/view_ref/' .$table_config_parent.'/'.$parent_id  );
		}
		
	}
	
	
	/*
	 * Fonction pour la suppression d'un element
	 * Input: 	$ref_table : nom de la structure de la table ou se trouve l'élément à supprimer
	 * 			$row_id : id de l'élément à supprimer
	 * 			$redirect: Y/N rediriger vers la liste d'éléments
	 */
	public function delete_ref ($ref_table,$row_id,$redirect=true){
	
		/*
		 * Appel de la foction dans le model pour appeler la requetter  de suppression de l'élément
		 */
		$res=$this->DBConnection_mdl->remove_element($row_id,$ref_table);
		
		
		/*
		 * Message de confirmation ou erreur
		 */
		if($res){
			set_top_msg("Success!");
		}
		else{
			set_top_msg(" Operation failed ",'error');
		}
		
		/*
		 * 
		 * Rédirection après l'opération si $redirect=true
		 */
		if($redirect)
		redirect ( 'manage/liste_ref/' .$ref_table  );
		
	}
	
	
	public function get_classification_scheme(){
		
		$result = $this->DBConnection_mdl->get_classification_scheme();
		$fields=array();
		foreach ($result as $key => $value) {
			
			
			$temp=$value;
			
			
			unset($temp['scheme_id']);
			unset($temp['field_label']);
			unset($temp['scheme_active']);
			unset($temp['field_order']);
			
			if($value['input_type']=='select'){
				if($value['input_select_source']=='yes_no'){
					
					$temp['input_select_source']="array";
					$temp['input_select_values']=array( ""=>"Select",
							0=>'No',
							1=>'Yes',
					);
				}elseif( $value['input_select_source']=='array' ){
					
					$Tvalues=explode(',',$value['input_select_values']);
					
					foreach ($Tvalues as $k => $v) {
						$Tvalues[$k]=$v;
						$val[$v]=$v;
					}
					
					$temp['input_select_values']=$val;
				}
			
			}
			
			$fields[$value['field_label']]=$temp;
			
		
		}
		
		print_test($fields);
	}
	
	
	
	
	
	
	

	
	
	private function get_extra_fields($class_id){
		
		
		//scope
		$result=$this->DBConnection_mdl->get_extra_fields($class_id);
		
		
		
		return $result;
		
		
		
	}
	
	public function result_export_papers(){
		//get classification
		
		$table_ref="papers";
		$this->db2 = $this->load->database(project_db(), TRUE);
		$sql="SELECT id,bibtexKey,title,doi,preview,bibtex FROM `paper`WHERE `paper_active` =1";
		$data=$this->db2->query ( $sql );
	//	mysqli_next_result( $this->db2->conn_id );
		$result=$data->result_array();
		//print_test($result);
		
		
		$array_header=array('#',"key",'Title','Link','Abstract','Preview');
		//print_test($result);
		//print_test($array_header);
		array_unshift($result, $array_header);
		//print_test($result);
		
		
		// Create a stream opening it with read / write mode
		$stream = fopen('data://text/plain,' . "", 'w+');
		
		// Iterate over the data, writting each line to the text stream
		$f_new = fopen("cside/export_r/export_paper_".project_db().".csv", 'w+');
		foreach ($result as $val) {
			fputcsv($f_new, $val,get_appconfig_element('csv_field_separator_export'));
		}
		
		// Rewind the stream
		//rewind($stream);
		
		// You can now echo it's content
		//echo stream_get_contents($stream);
		
		// Close the stream
		fclose($f_new);
		
		
		redirect('home/export');
		
	}
	
	
	public function result_export_classification(){
		//get classification
	
		$table_ref="classification";
		$this->db2 = $this->load->database(project_db(), TRUE);
		$data=$this->db2->query ( "CALL get_list_".$table_ref."(0,0,'') " );
		mysqli_next_result( $this->db2->conn_id );
		$result=$data->result_array();
		//print_test($result);
	
		$ref_table_config=$this->ref_table_config($table_ref);
	
		$dropoboxes=array();
		foreach ($ref_table_config['fields'] as $k => $v) {
	
			if(!empty($v['input_type']) AND $v['input_type']=='select' AND $v['on_list']=='show'){
				if($v['input_select_source']=='array'){
					$dropoboxes[$k]=$v['input_select_values'];
				}elseif($v['input_select_source']=='table'){
					$dropoboxes[$k]= $this->get_reference_select_values($v['input_select_values']);
				}elseif($v['input_select_source']=='yes_no'){
					$dropoboxes[$k]=array('0'=>"No",
							'1'=>"Yes"
					);
				}
			}
			;
		}
	
	
		//print_test($dropoboxes);
	
	
	
		foreach ($result as $key => $value) {
	
			/*
			 * Remplacement des clés externes par leurs correspondances
			 */
			foreach ($dropoboxes as $k => $v) {
	
					
				if($result [$key] [$k]  ){
	
					if( isset($v[$result [$key] [$k]]) ){
	
	
						$result [$key] [$k]=$v[$result [$key] [$k]];
	
					}
				}
				else
				{
					if($ref_table_config['fields'][$k]['field_value']=="0_1"){
						$result [$key] [$k]="No";
					}else{
						$result [$key] [$k]="";
							
					}
				}
					
					
			}
	
				
		}
	
		$array_header=$ref_table_config['header_list_fields'];
		//print_test($result);
		//print_test($array_header);
		array_unshift($result, $array_header);
		//print_test($result);
	
	
		// Iterate over the data, writting each line to the text stream
		$f_new = fopen("cside/export_r/export_classification_".project_db().".csv", 'w+');
		foreach ($result as $val) {
			fputcsv($f_new, $val,get_appconfig_element('csv_field_separator_export'));
		}
	
		
		fclose($f_new);
	
	
		redirect('home/export');
	
	}
	
	
	//////////////////used for test
	public function save_element_picture() {
	
		/*
		 * Récuperation des valeurs soumis dans le formulaire
		 */
		$post_arr = $this->input->post ();
	
		print_test($post_arr);
	
		if(isset($_FILES))
				
			print_test($_FILES);
	
				
			if (isset($_FILES['user_picture']) && $_FILES['user_picture']['size'] > 0) {
					
					
				// Temporary file name stored on the server
					
				$tmpName = $_FILES['user_picture']['tmp_name'];
					
					
				// Read the file
					
				$fp = fopen($tmpName, 'r');
					
				$data = fread($fp, filesize($tmpName));
					
				$data = addslashes($data);
					
				fclose($fp);
					
					
					
				// Create the query and insert
					
				// into our database.
					
				$query = "INSERT INTO picture_test ";
					
				$query .= "(picture_s,name) VALUES ('$data','".$_FILES['user_picture']['name']." ')";
					
				//	echo "<h2>$query</h2>";
				$res=$this->db->query($query);
					
				print_test($res);
					
					
					
			}
				
			else {
					
				print "No image selected/uploaded";
					
			}
	
	}
	
	
	public function get_element_picture($id=2) {
		$sql= "SELECT * from users where 	user_id =$id";
	
		$res= $this->db->query($sql)->row_array();
		$content = $res['user_picture'];
		//	print_test($res);
		//header('Content-type: image/jpg');
		//	echo $content;
		echo '<img src="data:image/png;base64,'.base64_encode( $content).'"/>';
	}
	
	
	
}
