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
function get_reference($table,$title,$config_id,$value_label="Value",$description_label="Description") {

	$config['config_id']=$config_id;
	$config['table_name']=$table;
	$config['table_id']='ref_id';
	$config['table_active_field']='ref_active';
	$config['main_field']='ref_value';
	
	$config['entity_label']=$title;
	$config['entity_label_plural']=$title;
	 
	//list view
	$config['order_by']='ref_value ASC '; //mettre la valeur à mettre dans la requette
	$config['search_by']='ref_value';// separer les champs par virgule
	
	
	 
	$fields['ref_id']=array(
			'field_title'=>'#',
			'field_type'=>'int',
			'field_size'=>11,
	   		'field_value'=>'auto_increment',
			'default_value'=>'auto_increment'
	);
	 
	 
	$fields['ref_value']=array(
			'field_title'=>$value_label,
			'field_type'=>'text', 
			'field_size'=>100,  
			'input_type'=>'text',
			'mandatory'=>' mandatory '
	);
	 
	$fields['ref_desc']=array(
			'field_title'=>$description_label,
			'field_type'=>'text', 
			'field_size'=>1000,  
			'input_type'=>'textarea',
	);
	 
	$fields['ref_active']=array(
			'field_title'=>'Active',
	   		'field_type'=>'int',
	   		'field_size'=>'1',
	   		'field_value'=>'1',
			'default_value'=>'1'
	);
	$config['fields']=$fields;
	 
	$operations['list_'.$config_id]=array(
			'operation_type'=>'List',
			'operation_title'=>'List of '.$title,
			'operation_description'=>'List of '.$title,
			'page_title'=>'List  of '.$title,
		  
			//'page_template'=>'list',
		  
			'data_source'=>'get_list_'.$config_id,
			'generate_stored_procedure'=>True,
	
			'fields'=>array(
				//	'ref_id'=>array(),
					'ref_value'=>array('link'=>array(
								'url'=>'op/edit_element/edit_'.$config_id.'/',
								'id_field'=>'ref_id',
								'trim'=>'0'
							)),
					'ref_desc'=>array()		   	
	
			),
			'order_by'=>'ref_value ASC ',
			'search_by'=>'ref_value',
			 
			 
			'list_links'=>array(
					/*
					'edit'=>array(
							'label'=>'Edit',
							'title'=>'Edit',
							'icon'=>'edit',
							'url'=>'op/edit_element/edit_'.$config_id.'/',
								),*/
					'delete'=>array(
							'label'=>'Delete',
							'title'=>'Delete the user',
							'icon'=>'trash',
							'url'=>'op/delete_element/remove_'.$config_id.'/'
					) 
			),
		  
			'top_links'=>array(
							'add'=>array(
										'label'=>'',
										'title'=>'Add '.$title,
										'icon'=>'add',
										'url'=>'op/add_element/add_'.$config_id,
									),
							'back'=>array(
										'label'=>'',
										'title'=>'Close',
										'icon'=>'',
										'url'=>'home',
									)
				
				),
		
			
	);
	
	
	$operations['add_'.$config_id]=array(
			'operation_type'=>'Add',
			'operation_title'=>'Add '.$title,
			'operation_description'=>'Add '.$title,
			'page_title'=>'Add a '.$title,
			'save_function'=>'op/save_element',
			'page_template'=>'general/frm_entity',
			'redirect_after_save'=>'op/entity_list/list_'.$config_id,
			'db_save_model'=>'add_'.$config_id,
	
			'generate_stored_procedure'=>True,
				
			'fields'=>array(
					'ref_id'=>array('mandatory'=>'','field_state'=>'hidden'),
					'ref_value'=>array('mandatory'=>'mandatory','field_state'=>'enabled'),
					'ref_desc'=>array('mandatory'=>'','field_state'=>'enabled'),
					
						
			),
	
			'top_links'=>array(
						
					'back'=>array(
							'label'=>'',
							'title'=>'Close',
							'icon'=>'close',
							'url'=>'home',
					)
	
			),
				
	);
	
	$operations['edit_'.$config_id]=array(
			'operation_type'=>'Edit',
			'operation_title'=>'Edit '.$title,
			'operation_description'=>'Edit '.$title,
			'page_title'=>'Edit '.$title,
			'save_function'=>'op/save_element',
			'page_template'=>'general/frm_entity',
	
			'redirect_after_save'=>'op/entity_list/list_'.$config_id,
			'data_source'=>'get_detail_'.$config_id,
			'db_save_model'=>'update_'.$config_id,
	
			'generate_stored_procedure'=>True,
				
			'fields'=>array(
					'ref_id'=>array('mandatory'=>'','field_state'=>'hidden'),
					'ref_value'=>array('mandatory'=>'mandatory','field_state'=>'enabled'),
					'ref_desc'=>array('mandatory'=>'','field_state'=>'enabled'),
						
			),
	
			'top_links'=>array(
						
					'back'=>array(
							'label'=>'',
							'title'=>'Close',
							'icon'=>'close',
							'url'=>'home',
					)
	
			),
				
	);
	
	$operations['detail_'.$config_id]=array(
			'operation_type'=>'Detail',
			'operation_title'=>'Detail of a '.$title,
			'operation_description'=>'Detail of a '.$title,
			'page_title'=>$title,
	
			//'page_template'=>'general/display_element',
	
			'data_source'=>'get_detail_'.$config_id,
			'generate_stored_procedure'=>True,
				
			'fields'=>array(
					//	'user_id'=>array(),
					'ref_value'=>array(),
					'ref_desc'=>array(),
						
			),
	
	
			'top_links'=>array(
					'edit'=>array(
							'label'=>'',
							'title'=>'Edit',
							'icon'=>'edit',
							'url'=>'op/edit_element/edit_'.$config_id.'/~current_element~',
					),
					'back'=>array(
							'label'=>'',
							'title'=>'Close',
							'icon'=>'add',
							'url'=>'home',
					),
	
	
	
			),
	);
	
	$operations['remove_'.$config_id]=array(
			'operation_type'=>'Remove',
			'operation_title'=>'Remove '.$title,
			'operation_description'=>'Remove '.$title,
			//'page_title'=>'Remove user '.active_user_name(),
	
			//'page_template'=>'detail',
			'redirect_after_delete'=>'op/entity_list/list_'.$config_id,
			'db_delete_model'=>'remove_'.$config_id,
			'generate_stored_procedure'=>True,
				
	
	);
	$config['operations']=$operations;
	
	return $config;

}