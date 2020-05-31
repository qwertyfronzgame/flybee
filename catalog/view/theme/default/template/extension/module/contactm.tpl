<div class="container">
<div class="panel panel-default contactm" style="margin: 5% 0; padding: 1%;">
  <div class="panel-heading bs-flex1"><img src="/images/Group2.png" alt=""> <h2><?php echo $heading_title; ?><h2></div>
  <div class="panel-body">
  
        <form class="form-horizontal" id="form-contactm">
          <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-6">
              <p class="feedback-p">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam suscipit maximus ultricies. Donec tempor nulla sit amet mi tincidunt malesuada. 
              </p>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-6">
        <fieldset class="row">
          <div class="form-group required col-sm-6">
            <div class="col-sm-12">
              <input type="text" name="tel" placeholder="<?php echo $entry_name; ?>"value="" id="input-name" class="form-control bs-input-ct" />
            </div>
          </div>
          <div class="form-group required col-sm-6">
            <div class="col-sm-12">
              <input type="text" name="tex" placeholder="<?php echo $entry_email; ?>" value="" id="input-email" class="form-control bs-input-ct" />
            </div>
          </div>
        </fieldset>
      </div>
      </div>
        <div class="buttons">
          <div class="pull-right">
            <input class="btn btn-primary" type="submit"  value="<?php echo $button_submit; ?>" />
          </div>
        </div>
      </form>
  
  
  </div>
</div>
</div>
<script type="text/javascript"><!--

$('.contactm .submit').on('click', function() {
	$.ajax({
		url: 'index.php?route=extension/module/contactm/send',
		type: 'post',
		dataType: 'json',
		data: $("#form-contactm").serialize(),
		beforeSend: function() {},
		complete: function() {},
		success: function(json) {
			$('.alert-success, .alert-danger').remove();

			if (json['error']) {
				$('.buttons').before('<div class="alert alert-danger col-sm-offset-2 col-sm-10  text-center"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + '</div>');
			}

			if (json['success']) {
				$('.buttons').before('<div class="alert alert-success col-sm-offset-2 col-sm-10  text-center"><i class="fa fa-check-circle"></i> ' + json['success'] + '</div>');

			}
		}
	});
});

//--></script>