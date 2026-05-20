<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<?php
$s   = $settings; // shorthand
$tab = $tab ?: 'general';
function sv($s, $key, $default = '') {
    return htmlspecialchars(isset($s[$key]) ? (string)$s[$key] : $default);
}
$logo_url = (!empty($s['site_logo']) && file_exists(FCPATH.'uploads/logo/'.$s['site_logo']))
            ? base_url('uploads/logo/'.$s['site_logo']) : '';
?>

<div class="box">
  <div class="box-header with-border">
    <h3 class="box-title"><i class="fa fa-cog text-saffron"></i> Application Settings</h3>
  </div>
  <div class="box-body">

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e): ?><li><?php echo $e; ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
      <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <!-- Tab nav -->
    <ul class="nav nav-tabs" id="settingsTabs">
      <li <?php echo $tab==='general' ?'class="active"':''; ?>>
        <a href="#st-general" data-toggle="tab"><i class="fa fa-home"></i> General</a>
      </li>
      <li <?php echo $tab==='contact' ?'class="active"':''; ?>>
        <a href="#st-contact" data-toggle="tab"><i class="fa fa-phone"></i> Contact</a>
      </li>
      <li <?php echo $tab==='social' ?'class="active"':''; ?>>
        <a href="#st-social" data-toggle="tab"><i class="fa fa-share-alt"></i> Social Media</a>
      </li>
      <li <?php echo $tab==='footer' ?'class="active"':''; ?>>
        <a href="#st-footer" data-toggle="tab"><i class="fa fa-file-text-o"></i> Footer</a>
      </li>
      <li <?php echo $tab==='seo' ?'class="active"':''; ?>>
        <a href="#st-seo" data-toggle="tab"><i class="fa fa-search"></i> SEO</a>
      </li>
    </ul>

    <div class="tab-content" style="padding-top:20px">

      <!-- ══ GENERAL ══════════════════════════════════════════ -->
      <div class="tab-pane <?php echo $tab==='general'?'active':''; ?>" id="st-general">
        <form method="post" action="<?php echo site_url('admin-settings'); ?>" enctype="multipart/form-data">
          <input type="hidden" name="settings_tab" value="general">
          <div class="row">

            <div class="col-md-6">
              <div class="form-group">
                <label><i class="fa fa-font"></i> Site Name</label>
                <input type="text" class="form-control" name="site_name"
                       value="<?php echo sv($s,'site_name','myeoncasuals'); ?>" required>
                <small class="text-muted">Shown in browser tab and throughout the site.</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><i class="fa fa-tag"></i> Site Tagline</label>
                <input type="text" class="form-control" name="site_tagline"
                       value="<?php echo sv($s,'site_tagline','Pure & Natural'); ?>">
                <small class="text-muted">Shown beneath the logo in the header.</small>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label><i class="fa fa-image"></i> Site Logo</label>
                <div class="row">
                  <div class="col-md-2">
                    <?php if ($logo_url): ?>
                      <img id="logoPreview" src="<?php echo $logo_url; ?>"
                           style="max-height:60px;max-width:100%;border-radius:6px;border:1px solid #ddd">
                    <?php else: ?>
                      <div id="logoPreview"
                           style="width:80px;height:60px;background:#f4f4f4;border:1px dashed #ccc;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#aaa;font-size:.75rem">
                        No Logo
                      </div>
                    <?php endif; ?>
                  </div>
                  <div class="col-md-10">
                    <input type="file" class="form-control" name="site_logo"
                           accept="image/*" id="logoFileInput">
                    <small class="text-muted">PNG/JPG/SVG/WebP · max 1 MB · Recommended: 200×60 px transparent PNG.</small>
                    <?php if ($logo_url): ?>
                      <div class="margin-t-5">
                        <span class="label label-success"><i class="fa fa-check"></i> Logo uploaded</span>
                        <small class="text-muted ms-2"><?php echo sv($s,'site_logo'); ?></small>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-12">
              <div class="form-group">
                <label><i class="fa fa-bars"></i> Top Strip Text</label>
                <input type="text" class="form-control" name="top_strip_text"
                       value="<?php echo sv($s,'top_strip_text'); ?>">
                <small class="text-muted">The red banner at the very top of every page. Leave blank to hide it.</small>
              </div>
            </div>

          </div>
          <button type="submit" class="btn btn-saffron">
            <i class="fa fa-save"></i> Save General Settings
          </button>
        </form>
      </div>

      <!-- ══ CONTACT ══════════════════════════════════════════ -->
      <div class="tab-pane <?php echo $tab==='contact'?'active':''; ?>" id="st-contact">
        <form method="post" action="<?php echo site_url('admin-settings'); ?>">
          <input type="hidden" name="settings_tab" value="contact">
          <div class="row">

            <div class="col-md-6">
              <div class="form-group">
                <label><i class="fa fa-phone"></i> Phone Number</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                  <input type="text" class="form-control" name="contact_phone"
                         value="<?php echo sv($s,'contact_phone'); ?>"
                         placeholder="+91 98765 43210">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><i class="fa fa-envelope"></i> Email Address</label>
                <div class="input-group">
                  <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                  <input type="email" class="form-control" name="contact_email"
                         value="<?php echo sv($s,'contact_email'); ?>"
                         placeholder="hello@myeoncasuals.com">
                </div>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label><i class="fa fa-map-marker"></i> Address</label>
                <textarea class="form-control" name="contact_address" rows="3"
                          placeholder="Full address shown in footer"><?php echo sv($s,'contact_address'); ?></textarea>
              </div>
            </div>

          </div>
          <button type="submit" class="btn btn-saffron">
            <i class="fa fa-save"></i> Save Contact Settings
          </button>
        </form>
      </div>

      <!-- ══ SOCIAL ════════════════════════════════════════════ -->
      <div class="tab-pane <?php echo $tab==='social'?'active':''; ?>" id="st-social">
        <form method="post" action="<?php echo site_url('admin-settings'); ?>">
          <input type="hidden" name="settings_tab" value="social">
          <p class="text-muted small margin-b-15">
            <i class="fa fa-info-circle"></i>
            Enter full URLs (e.g. <code>https://facebook.com/yourpage</code>). Leave blank to hide the icon.
          </p>
          <div class="row">

            <?php
            $socials = array(
              'social_facebook'  => array('fa-facebook-square', 'Facebook',  '#3b5998'),
              'social_instagram' => array('fa-instagram',       'Instagram', '#e1306c'),
              'social_youtube'   => array('fa-youtube-play',    'YouTube',   '#ff0000'),
              'social_whatsapp'  => array('fa-whatsapp',        'WhatsApp',  '#25d366'),
              'social_twitter'   => array('fa-twitter-square',  'Twitter/X', '#1da1f2'),
            );
            foreach ($socials as $key => $meta):
              list($icon, $label, $color) = $meta;
            ?>
            <div class="col-md-6">
              <div class="form-group">
                <label>
                  <i class="fa <?php echo $icon; ?>" style="color:<?php echo $color; ?>"></i>
                  <?php echo $label; ?>
                </label>
                <input type="url" class="form-control" name="<?php echo $key; ?>"
                       value="<?php echo sv($s, $key); ?>"
                       placeholder="https://">
              </div>
            </div>
            <?php endforeach; ?>

          </div>
          <button type="submit" class="btn btn-saffron">
            <i class="fa fa-save"></i> Save Social Links
          </button>
        </form>
      </div>

      <!-- ══ FOOTER ════════════════════════════════════════════ -->
      <div class="tab-pane <?php echo $tab==='footer'?'active':''; ?>" id="st-footer">
        <form method="post" action="<?php echo site_url('admin-settings'); ?>">
          <input type="hidden" name="settings_tab" value="footer">
          <div class="row">

            <div class="col-md-12">
              <div class="form-group">
                <label><i class="fa fa-align-left"></i> About Text <small class="text-muted">(footer column 1)</small></label>
                <textarea class="form-control" name="footer_about" rows="4"
                          placeholder="Short description about your store..."><?php echo sv($s,'footer_about'); ?></textarea>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label><i class="fa fa-copyright"></i> Copyright Text</label>
                <div class="input-group">
                  <span class="input-group-addon">© <?php echo date('Y'); ?></span>
                  <input type="text" class="form-control" name="footer_copyright"
                         value="<?php echo sv($s,'footer_copyright','myeoncasuals. All rights reserved.'); ?>">
                </div>
                <small class="text-muted">Shown at the very bottom of the page.</small>
              </div>
            </div>

          </div>
          <button type="submit" class="btn btn-saffron">
            <i class="fa fa-save"></i> Save Footer Settings
          </button>
        </form>
      </div>

      <!-- ══ SEO ═══════════════════════════════════════════════ -->
      <div class="tab-pane <?php echo $tab==='seo'?'active':''; ?>" id="st-seo">
        <form method="post" action="<?php echo site_url('admin-settings'); ?>">
          <input type="hidden" name="settings_tab" value="seo">
          <div class="row">

            <div class="col-md-12">
              <div class="form-group">
                <label><i class="fa fa-header"></i> Default Meta Title</label>
                <input type="text" class="form-control" name="meta_title"
                       value="<?php echo sv($s,'meta_title'); ?>"
                       placeholder="myeoncasuals – Trendy Casual Wear"
                       maxlength="70">
                <small class="text-muted">
                  <span id="metaTitleCount"><?php echo strlen($s['meta_title'] ?? ''); ?></span>/70 characters · Used when a page has no custom meta title.
                </small>
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label><i class="fa fa-align-justify"></i> Default Meta Description</label>
                <textarea class="form-control" name="meta_desc" rows="3"
                          maxlength="160"
                          id="metaDescField"
                          placeholder="Describe your store in 120–160 characters…"><?php echo sv($s,'meta_desc'); ?></textarea>
                <small class="text-muted">
                  <span id="metaDescCount"><?php echo strlen($s['meta_desc'] ?? ''); ?></span>/160 characters
                </small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label><i class="fa fa-google"></i> Google Analytics ID</label>
                <input type="text" class="form-control" name="google_analytics"
                       value="<?php echo sv($s,'google_analytics'); ?>"
                       placeholder="G-XXXXXXXXXX or UA-XXXXXXXX-X">
                <small class="text-muted">Leave blank to disable tracking.</small>
              </div>
            </div>

          </div>
          <button type="submit" class="btn btn-saffron">
            <i class="fa fa-save"></i> Save SEO Settings
          </button>
        </form>
      </div>

    </div><!-- /tab-content -->
  </div><!-- /box-body -->
</div>
