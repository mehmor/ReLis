<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ReLis | Login </title>

    <!-- Bootstrap -->
    <link href="<?php echo site_url();?>cside/vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="<?php echo site_url();?>cside/vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="<?php echo site_url();?>cside/css/custom.css" rel="stylesheet">
  </head>

  <body style="background:#F7F7F7;">
    <div class="">
      <a class="hiddenanchor" id="toregister"></a>
      <a class="hiddenanchor" id="tologin"></a>

      <div id="wrapper">
        <div id="login" class=" form">
          <section class="login_content">
            <form action="<?php echo base_url()?>auth/check_form" method="POST">
              <h1>ReLiS</h1><br/>
              <h2><?php echo lng_min('Log in')?></h2>
			  <?php
                    	if(validation_errors() OR isset($err_msg) OR ($this->session->userdata('page_msg_err')) )
						{
							echo '<div class="alert alert-danger" style="text-align:center">';
							echo validation_errors();
							
							 if (isset($err_msg))echo $err_msg;
							 
							 if (($this->session->userdata('page_msg_err'))){
							 	echo $this->session->userdata('page_msg_err');
								$this->session->set_userdata('page_msg_err','');
							 }
							echo "</div>";
						}
                    	?>
                    	<br/>
              <div>
                <input type="text" class="form-control" placeholder="<?php echo lng_min('Username')?>" name="user_username" value="" required="" />
              </div>
              <div>
                <input type="password" class="form-control" placeholder="<?php echo lng_min('Password')?>" name="user_password" value="" required="" />
              </div>
              <div>
                <button type="submit" class="btn btn-default submit" ><?php echo lng_min('Log in')?></button>
                
              </div>
              <div class="clearfix"></div>
              <div class="separator">

                
                <div class="clearfix"></div>
                <br />
                <div>
                  
                </div>
              </div>
            </form>
          </section>
        </div>
      </div>
    </div>
  </body>
</html>