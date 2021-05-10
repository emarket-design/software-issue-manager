<div id="emd-login-container">
<form id="emd_login_form" class="emdloginreg-container emd_form" action="<?php echo get_permalink($post->ID); ?>" method="post">
<fieldset>
<legend><?php _e( 'Log into Your Account', 'emd-plugins' ); ?></legend>
<div class="emd-form-row emd-row" style="display:flex;">
<div class="emd-form-field emd-col emd-md-12 emd-sm-12 emd-xs-12 emd-reg">
<div class="emd-form-group emd-login-username">
<label for="emd_user_login"><span><?php _e( 'Username or Email', 'emd-plugins' ); ?></span>
<span class="emd-fieldicons-wrap">
<a href="#" data-html="true" tabindex=-1 data-toggle="tooltip" title="<?php _e('Username or Email field is required','emd-plugins');?>" id="req_user_login" class="helptip">
<span class="field-icons required" aria-required="true"></span></a>
</span>
</label>
<input name="emd_user_login" id="emd_user_login" class="text required emd-input-md emd-form-control" type="text"/>
</div>
</div>
</div>
<div class="emd-form-row emd-row" style="display:flex;">
<div class="emd-form-field emd-col emd-md-12 emd-sm-12 emd-xs-12 emd-reg">
<div class="emd-form-group emd-login-password">
<label for="emd_user_pass"><span><?php _e( 'Password', 'emd-plugins' ); ?></span>
<span class="emd-fieldicons-wrap">
<a href="#" data-html="true" tabindex=-1 data-toggle="tooltip" title="<?php _e('Password field is required','emd-plugins');?>" id="req_user_pass" class="helptip">
<span class="field-icons required" aria-required="true"></span>
</span>
</label>
<input name="emd_user_pass" id="emd_user_pass" class="password required emd-input-md emd-form-control" type="password"/>
</div>
</div>
</div>
<div class="emd-form-group emd-login-remember">
<label><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php _e( 'Remember Me', 'emd-plugins' ); ?></label>
</div>
<div>
<input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink($post->ID)); ?>"/>
<input type="hidden" name="emd_login_nonce" value="<?php echo wp_create_nonce( 'emd-login-nonce' ); ?>"/>
<input type="hidden" name="emd_action" value="login"/>

<input type="submit" class="emd_submit button" id="emd-login-submit" value="<?php _e( 'Log In', 'emd-plugins' ); ?>"/>
</div>
<div style="clear:both">
<p class="emd-lost-password" style="float:left">
<a href="<?php echo wp_lostpassword_url(); ?>">
<?php _e( 'Lost Password?', 'emd-plugins' ); ?>
</a>
</p>
<p class="emd-register-link" style="float:right">
<a href="">
<?php _e( 'Register', 'emd-plugins' ); ?>
</a>
</p>
</div>
</fieldset><!--end #emd_login_fields-->
</form>
</div>
