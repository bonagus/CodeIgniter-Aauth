      <div class="card">
        <div class="card-header"><?=lang('Account.editHeader')?></div>
        <div class="card-body">
          <form method="POST">
						<?if (isset($errors)):?>
							<div class="alert alert-danger"><?=$errors?></div>
						<?endif;?>
						<?if (isset($infos)):?>
							<div class="alert alert-success"><?=$infos?></div>
						<?endif;?>
            <div class="form-group">
              <div class="form-label-group">
								<input type="email" name="email" id="inputEmail" class="form-control" placeholder="<?=lang('Account.editLabelEmail')?>" autofocus>
								<label for="inputEmail"><?=lang('Account.editLabelEmail')?></label>
              </div>
            </div>
            <div class="form-group">
              <div class="form-label-group">
								<input type="text" name="username" id="inputUsername" class="form-control" placeholder="<?=lang('Account.editLabelUsername')?>">
								<label for="inputUsername"><?=lang('Account.editLabelUsername')?></label>
              </div>
            </div>
            <div class="form-group">
              <div class="form-label-group">
								<input type="password" name="password" id="inputPassword" class="form-control" placeholder="<?=lang('Account.editLabelPassword')?>">
								<label for="inputPassword"><?=lang('Account.editLabelPassword')?></label>
              </div>
            </div>
						<button class="btn btn-primary btn-block" type="submit"><?=lang('Account.editLabelSubmit')?></button>
          </form>
        </div>
      </div>