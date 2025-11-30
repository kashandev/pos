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
				  	<div class="panel-heading heading">
						<?php echo $heading_title; ?>
 <a onclick="location = '<?php echo $cancel; ?>';" class="insert-btn"><span><?php echo $button_cancel; ?></span></a>                   <a onclick="$('#form').submit();" class="insert-btn"><span><?php echo $button_save; ?></span></a>
					</div>
        		<div class="panel-body">
							<div class="row">
								<div class="col-lg-6">
            <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
               <div class="panel-body">
                            <div class="table-responsive">
                    <div class="form-group">
                       <label><?php echo $entry_name; ?></label>
                        <input type="text" name="name" value="<?php echo $name; ?>" class="form-control"/>
                            <?php if (isset($error['name'])) { ?>
                            <span class="error"><?php echo $error['name']; ?></span>
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