<?php
require_once(dirname(__DIR__) . '/config/site_config.php');
redirectIfLoggedOut('index.php');

waitForModeLock();
$mode = readCurrentMode($_DATABASE);
define('HIDDEN_STYLE', 'style="display: none;"');
?>

<!DOCTYPE html>
<html>

<head>
  <?php
  require_once(TEMPLATES_PATH . '/head_common.php');
  require_once(TEMPLATES_PATH . '/video-js_css.php');
  ?>

  <style>
    .checked {
      display: none;
    }

    .active .checked {
      display: inline-block;
    }

    .active .unchecked {
      display: none;
    }
  </style>

  <script>
    const SERVER_TIMESTAMP = <?php echo microtime(true); ?> * 1e3; // Milliseconds
    const CLIENT_TIMESTAMP = Date.now(); // Milliseconds
  </script>
  <script src="js/sync_time.js"></script>
</head>

<body>
  <div class="d-flex flex-column min-vh-100 vh-100">
    <header>
      <?php
      require_once(TEMPLATES_PATH . '/navbar.php');
      require_once(TEMPLATES_PATH . '/confirmation_modal.php');
      ?>
    </header>

    <main id="main" class="d-flex flex-column flex-grow-1 justify-content-center overflow-auto">

      <div class="container-fluid h-100">
        <div class="row h-100 align-items-center justify-content-center px-0">

          <div id="mode_content_listen" class="col-auto" <?php echo ($mode != MODE_VALUES['listen']) ? HIDDEN_STYLE : '' ?>>
            Varsle
          </div>

          <div id="mode_content_audio" class="col w-100" <?php echo ($mode != MODE_VALUES['audiostream']) ? HIDDEN_STYLE : '' ?>>
            <div class="row align-items-center justify-content-center" id="audiostream_visualization_mode_box" <?php echo HIDDEN_STYLE; ?>>
              <div class="col-auto fw-bold">
                Visualisering
              </div>
              <div class="col-auto">
                <form>
                  <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="audiostream_control_radio" id="audiostream_none_radio" onclick="switchAudioVisualizationModeTo(null);" checked>
                    <label class="form-check-label" for="none_radio">Ingen</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="audiostream_control_radio" id="audiostream_time_radio" onclick="switchAudioVisualizationModeTo('time');">
                    <label class="form-check-label" for="time_radio">Bølge</label>
                  </div>
                  <div class="form-check form-check-inline">
                    <input type="radio" class="form-check-input" name="audiostream_control_radio" id="audiostream_frequency_radio" onclick="switchAudioVisualizationModeTo('frequency');">
                    <label class="form-check-label" for="frequency_radio">Frekvenser</label>
                  </div>
                </form>
              </div>
            </div>
            <div class="row align-items-center justify-content-center" id="audiostream_fftsize_range_box" <?php echo HIDDEN_STYLE; ?>>
              <div class="col-auto">
                <label class="form-label" for="audiostream_fftsize_range">Målinger per bilde</label>
              </div>
              <div class="col-auto">
                <div class="row flex-nowrap">
                  <div class="col-10">
                    <input type="range" class="form-range" value="7" min="5" max="15" step="1" id="audiostream_fftsize_range" oninput="$('#audiostream_fftsize_range_value').html(2**this.value); switchFFTSizePowerTo(this.value);">
                  </div>
                  <div class="col-1">
                    <output id="audiostream_fftsize_range_value">128</output>
                  </div>
                </div>
              </div>
            </div>
            <div class="row justify-content-center" id="audiostream_canvas_box">
            </div>
            <div class="row justify-content-center" id="audiostream_player_box">
            </div>
          </div>

          <div id="mode_content_video" class="h-100 px-0" <?php echo ($mode != MODE_VALUES['videostream']) ? HIDDEN_STYLE : '' ?>>
            <div id="mode_content_video_box" class="h-100">
            </div>
          </div>

          <div id="mode_content_standby" class="col-auto text-center" <?php echo ($mode != MODE_VALUES['standby']) ? HIDDEN_STYLE : '' ?>>
            <svg class="bi" style="height: 50vh" fill="currentColor">
              <use xlink:href="media/bootstrap-icons.svg#moon-fill" />
            </svg>
            <p>Enheten er i hvilemodus</p>
          </div>

          <div id="mode_content_waiting" class="col-auto" <?php echo HIDDEN_STYLE; ?>>
            <span class="spinner-grow text-muted"></span>
          </div>

          <div id="mode_content_error" class="col-auto" <?php echo HIDDEN_STYLE; ?>>
            <div class="container">
              <div class="row">
                <span id="mode_content_error_message" class="alert alert-danger text-center">Error
                </span>
                <a class=" btn btn-secondary" href="main.php">Forny siden</a>
              </div>
            </div>
          </div>

        </div>
      </div>

    </main>

    <?php
    define('RADIO_ICON_STYLE', 'height: 4vh; width: 4vw;');
    ?>
    <footer class="d-flex flex-grow-0 flex-shrink-1 justify-content-center">
      <div class="btn-group" data-toggle="buttons">
        <label class="btn btn-primary <?php echo ($mode == MODE_VALUES['listen']) ? 'active' : '' ?> disabled" data-toggle="tooltip" title="Varslemodus">
          <input type="radio" class="btn-check" name="mode_radio" id="mode_radio_listen" autocomplete="off" disabled <?php echo ($mode == MODE_VALUES['listen']) ? 'checked' : '' ?> value="<?php echo MODE_VALUES['listen'] ?>">
          <svg class="bi unchecked" style="<?php echo RADIO_ICON_STYLE ?>" fill="currentColor">
            <use xlink:href="media/bootstrap-icons.svg#exclamation-circle" />
          </svg>
          <svg class="bi checked" style="<?php echo RADIO_ICON_STYLE ?>" fill="currentColor">
            <use xlink:href="media/bootstrap-icons.svg#exclamation-circle-fill" />
          </svg>
        </label>

        <label class="btn btn-primary <?php echo ($mode == MODE_VALUES['audiostream']) ? 'active' : '' ?> disabled" data-toggle="tooltip" title="Lyttemodus">
          <input type="radio" class="btn-check" name="mode_radio" id="mode_radio_audio" autocomplete="off" disabled <?php echo ($mode == MODE_VALUES['audiostream']) ? 'checked' : '' ?> value="<?php echo MODE_VALUES['audiostream'] ?>">
          <svg class="bi unchecked" style="<?php echo RADIO_ICON_STYLE ?>" fill="currentColor">
            <use xlink:href="media/bootstrap-icons.svg#mic" />
          </svg>
          <svg class="bi checked" style="<?php echo RADIO_ICON_STYLE ?>" fill="currentColor">
            <use xlink:href="media/bootstrap-icons.svg#mic-fill" />
          </svg>
        </label>

        <label class="btn btn-primary <?php echo ($mode == MODE_VALUES['videostream']) ? 'active' : '' ?> disabled" data-toggle="tooltip" title="Videomodus">
          <input type="radio" class="btn-check" name="mode_radio" id="mode_radio_video" autocomplete="off" disabled <?php echo ($mode == MODE_VALUES['videostream']) ? 'checked' : '' ?> value="<?php echo MODE_VALUES['videostream'] ?>">
          <svg class="bi unchecked" style="<?php echo RADIO_ICON_STYLE ?>" fill="currentColor">
            <use xlink:href="media/bootstrap-icons.svg#camera-video" />
          </svg>
          <svg class="bi checked" style="<?php echo RADIO_ICON_STYLE ?>" fill="currentColor">
            <use xlink:href="media/bootstrap-icons.svg#camera-video-fill" />
          </svg>
        </label>

        <label class="btn btn-primary <?php echo ($mode == MODE_VALUES['standby']) ? 'active' : '' ?> disabled" data-toggle="tooltip" title="Hvilemodus">
          <input type="radio" class="btn-check" name="mode_radio" id="mode_radio_standby" autocomplete="off" disabled <?php echo ($mode == MODE_VALUES['standby']) ? 'checked' : '' ?> value="<?php echo MODE_VALUES['standby'] ?>">
          <svg class="bi unchecked" style="<?php echo RADIO_ICON_STYLE ?>" fill="currentColor">
            <use xlink:href="media/bootstrap-icons.svg#moon" />
          </svg>
          <svg class="bi checked" style="<?php echo RADIO_ICON_STYLE ?>" fill="currentColor">
            <use xlink:href="media/bootstrap-icons.svg#moon-fill" />
          </svg>
        </label>
        <!-- <label class="btn btn-outline-primary" for="mode_radio_listen">Varsle</label> -->

      </div>
    </footer>
  </div>

  <?php
  require_once(TEMPLATES_PATH . '/bootstrap_js.php');
  require_once(TEMPLATES_PATH . '/video-js_js.php');
  require_once(TEMPLATES_PATH . '/jquery_js.php');
  ?>

  <script>
    const STANDBY_MODE = <?php echo MODE_VALUES['standby']; ?>;
    const AUDIOSTREAM_MODE = <?php echo MODE_VALUES['audiostream']; ?>;
    const VIDEOSTREAM_MODE = <?php echo MODE_VALUES['videostream']; ?>;
    const INITIAL_MODE = <?php echo $mode; ?>;

    const AUDIO_SRC = <?php echo '\'http://' . SERVER_IP . ':' . getenv('BM_MICSTREAM_PORT') . getenv('BM_MICSTREAM_ENDPOINT') . '\''; ?>;

    const SETTING_SAMPLING_RATE = <?php echo readValuesFromTable($_DATABASE, 'audiostream_settings', 'sampling_rate', true); ?>;
    const SETTING_VOLUME = <?php echo readValuesFromTable($_DATABASE, 'audiostream_settings', 'volume', true); ?>;
    const min_frequency = <?php echo readValuesFromTable($_DATABASE, 'audiostream_settings', 'min_frequency', true); ?>;
    const max_frequency = <?php echo readValuesFromTable($_DATABASE, 'audiostream_settings', 'max_frequency', true); ?>;
    const SETTING_MIN_FREQUENCY = Math.min(min_frequency, max_frequency);
    const SETTING_MAX_FREQUENCY = Math.max(min_frequency, max_frequency);
  </script>
  <script src="js/confirmation_modal.js"></script>
  <script src="js/navbar.js"></script>
  <script src="js/navbar_main.js"></script>
  <script src="js/audio_video.js"></script>
  <script src="js/audio.js"></script>
  <script src="js/video.js"></script>
  <script src="js/main.js"></script>

</body>

</html>
