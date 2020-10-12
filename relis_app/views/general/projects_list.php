	<!-- page content -->
        <div class="right_col" role="main">
        
        <?php top_msg(); 
        
        $configuration = get_appconfig();
        
        //print_test($configuration);
      //  print_test($users);
        
        ?> 
        
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
              <div class="dashboard_graph">

                <div class="row x_title">
                  <h2><?php echo isset($page_title) ? $page_title :"" ; ?></h2>
                    <?php 
                    
                    if(isset($top_buttons)){
                    	echo "<ul class='nav navbar-right panel_toolbox'>$top_buttons</ul>";
                    
                    }                    
                    ?>
                  
                </div>
                
				<div class="col-md-12 col-sm-12 col-xs-12 bg-white">
                 <?php 
              // 	print_test($projects['list']);
                 foreach ($projects['list'] as $key => $value) {
                 	
                 	$project_class=(project_published($value['project_id']) )?'publishedProject':'';
                 ?>
                 <a href="<?php echo base_url().'manager/set_project/'.$value['project_id']?>">
                 <div class="col-md-3 col-sm-3 col-xs-12 col-md-offset-1">
                        <div class="thumbnail <?php echo $project_class ?>">
                          <div class="image view view-first">
                            <img style="height: 100%; display: block;" src="<?php echo $value['icon']?>" alt="<?php echo $value['project_description']?>" />
                            <div class="mask" >
                              <p><?php echo $value['project_description']?></p>
                              <div class="tools tools-bottom">
                              <?php 
                              echo anchor('manager/set_project/'.$value['project_id'],' &nbsp &nbsp <i class="fa fa-paper-plane"></i> ','title="'.lng_min('Go to the project').'"');
                              if(is_project_creator($value['project_label']) or has_usergroup(1)){
                              echo anchor('op/display_element/detail_project/'.$value['project_id'],'  &nbsp &nbsp <i class="fa fa-folder"></i>','title="'.lng_min('View').'"');
                              echo anchor('op/edit_element/edit_project/'.$value['project_id'],' &nbsp &nbsp <i class="fa fa-pencil"></i>','title="'.lng_min('Edit').'"');
                              echo anchor('install/remove_project_validation/'.$value['project_id'],' &nbsp &nbsp <i class="fa fa-trash-o"></i>','title="'.lng_min('Uninstall').'"');
                              }
                              ?>
                              </div>
                            </div>
                          </div>
                          <div class="caption">
                            <p><?php echo $value['project_title']?></p>
                          </div>
                        </div>
                      </div>
                      </a>
                 <?php }
                 
                 echo"<div style='text-align:center; padding:20px;'>";
                 if(empty($projects['list'] )){
                 	// add new project button
                 	
	                 	echo"<p>";
	                 	echo lng('No project available!');
	                 	echo"</p><br/><br/>";
                  }	
                 	if(isset($add_project_button))
                 	echo $add_project_button;
                 
                 echo"</div>";
                 
                 
                 ?>
                </div>
                
                

                <div class="clearfix"></div>
              </div>
            </div>

          </div>
          
         <br/>
        
        </div>
       

        <!-- /page content -->