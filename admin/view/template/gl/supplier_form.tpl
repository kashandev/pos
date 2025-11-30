<?php echo $header; ?>
<?php echo $column_left; ?>
<div id="page-wrapper">
<!--    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>-->
    <?php if ($error_warning) { ?>
    <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>
	<div class="row">
    	<div class="box">
        <!--<div class="heading">
            <h1><img src="view/image/user.png" alt="" /> <?php echo $heading_title; ?></h1>
            <div class="buttons">
                <a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a>
                <a onclick="location = '<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a>
            </div>
        </div>-->
		<div class="col-lg-12">
				  <div class="panel panel-default">
				  	<div class="panel-heading">
						<?php echo $heading_title; ?>
					</div>
        		<div class="panel-body">
							<div class="row">
								<div class="col-lg-6">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
					<div class="panel-body">
                            <div class="table-responsive">
							
							<div class="form-group">	
							<label><?php echo $entry_name; ?></label>
									<input type="text" name="name" value="<?php echo $name; ?>" class="form-control" />
									<?php if (isset($error['name'])) { ?>
									<span class="error"><?php echo $error['name']; ?></span>
									<?php } ?>
							</div>					
							
							<div class="form-group">
								<label><?php echo $entry_industry; ?></label>
									<select class="form-control" id="industry_id" name="industry_id" >
										<?php foreach($industrys as $id => $value): ?>
										<option value="<?php echo $id; ?>" <?php echo ($id == $industry_id ? 'selected="selected"' : ''); ?>><?php echo $value; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								
								<div class="form-group">									
								<label><?php echo $entry_gst_no; ?></label>
									<input type="text" name="gst_no" value="<?php echo $gst_no; ?>" class="form-control"/>
									<?php if (isset($error['gst_no'])) { ?>
									<span class="error"><?php echo $error['gst_no']; ?></span>
									<?php } ?>
								</div>	
								
								<div class="form-group">
								<label><?php echo $entry_currency; ?></label>
									<select class="form-control" id="currency_id" name="currency_id" >
										<?php foreach($currencys as $id => $value): ?>
										<option value="<?php echo $id; ?>" <?php echo ($id == $currency_id ? 'selected="selected"' : ''); ?>><?php echo $value; ?></option>
										<?php endforeach; ?>
									</select>
								</div>	
								
							<div class="form-group">
								<label><?php echo $entry_phone; ?></label>
									<input type="text" name="phone" value="<?php echo $phone; ?>" class="form-control" />
									<?php if (isset($error['phone'])) { ?>
									<span class="error"><?php echo $error['phone']; ?></span>
									<?php } ?>
							</div>
									
							<div class="form-group">
								<label><?php echo $entry_fax; ?></label>
									<input type="text" name="fax" value="<?php echo $fax; ?>" class="form-control"/>
									<?php if (isset($error['fax'])) { ?>
									<span class="error"><?php echo $error['fax']; ?></span>
									<?php } ?>
								</div>
								
								
							<div class="form-group">
								<label><?php echo $entry_email; ?></label>
									<input type="text" name="email" value="<?php echo $email; ?>" class="form-control" />
									<?php if (isset($error['email'])) { ?>
									<span class="error"><?php echo $error['email']; ?></span>
									<?php } ?>
								</div>	
								
							<div class="form-group">
								<label><?php echo $entry_address; ?></label> 
									<textarea class="form-control" name="address" cols="30" rows="3">
									<?php echo $address; ?></textarea>
									<?php if (isset($error['address'])) { ?>
									<span class="error"><?php echo $error['address']; ?></span>
									<?php } ?>
								</div>
								
								
								<div class="form-group">
								<label><?php echo $entry_debit_limit; ?> </label>
									<input type="text" name="debit_limit" value="<?php echo $debit_limit; ?>" class="form-control" />
									<?php if (isset($error['debit_limit'])) { ?>
									<span class="error"><?php echo $error['debit_limit']; ?></span>
									<?php } ?>
								</div>
								
								
							<div class="form-group">
								<label><?php echo $entry_status; ?></label>
									<select class="form-control" name="status">
										<option value="1" <?php echo ($status == 1 ? 'selected="selected"' :''); ?>>
										<?php echo $text_enabled; ?></option>
										<option value="2" <?php echo ($status == 2 ? 'selected="selected"' :''); ?>>
										<?php echo $text_disabled; ?></option>
									</select>
								</div>
<a onclick="$('#form').submit();" class="btn btn-default"><span><?php echo $button_save; ?></span></a>
    <a onclick="location = '<?php echo $cancel; ?>';" class=" btn btn-default"><span><?php echo $button_cancel; ?></span></a>
								
					</div>	
				</div>		
            </form>
			        </div>
				 </div>	
			   </div>
			</div>
		  </div>	
        </div>
    </div>
</div>
<?php echo $footer; ?>