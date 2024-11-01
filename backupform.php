<?php
$path = get_home_path();

if (current_user_can('administrator')) {
  ?>

  <style type="text/css">

    body {
      font-family: Arial, sans-serif;
      line-height: 150%;
    }

    label {
      display: block;
      margin-top: 20px;
    }

    fieldset {
      border: 0;
      background-color: #EEE;
      margin: 10px 0 10px 0;
    }

    .select {
      padding: 5px;
      font-size: 110%;
    }

    .status {
      margin: 0;
      margin-bottom: 20px;
      padding: 10px;
      font-size: 80%;
      background: #EEE;
      border: 1px dotted #DDD;
    }

    .status--ERROR {
      background-color: red;
      color: white;
      font-size: 120%;
    }

    .status--SUCCESS {
      background-color: green;
      font-weight: bold;
      color: white;
      font-size: 120%
    }

    .small {
      font-size: 0.7rem;
      font-weight: normal;
    }

    .version {
      font-size: 80%;
    }

    .form-field {
      border: 1px solid #AAA;
      padding: 8px;
      width: 280px;
    }

    .info {
      margin-top: 0;
      font-size: 80%;
      color: #777;
    }

    .submit {
      background-color: #378de5;
      border: 0;
      color: #ffffff;
      font-size: 15px;
      padding: 10px 24px;
      margin: 20px 0 20px 0;
      text-decoration: none;
    }

    .submit:hover {
      background-color: #2c6db2;
      cursor: pointer;
    }

    .xeroxbackup_col-lg-8 {

      float: left;
      max-width: 60%;
      width: 100%;
    }

    .xeroxbackup_col-lg-4 {

      float: left;
      max-width: 30%;
      width: 100%;
    }

    .xerox_backup_loader {
      border: 16px solid #f3f3f3;
      border-radius: 50%;
      border-top: 16px solid #3498db;
      width: 120px;
      height: 120px;
      -webkit-animation: spin 2s linear infinite; /* Safari */
      animation: spin 2s linear infinite;
    }

    /* Safari */
    @-webkit-keyframes spin {
      0% {
        -webkit-transform: rotate(0deg);
      }
      100% {
        -webkit-transform: rotate(360deg);
      }
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }
  </style>
  <div class="xeroxbackup_col-lg-8">

    <form action="" method="POST" id="xeroxbackuping">

      <fieldset>
        <h1>Take Backup</h1>
        <input type="submit" name="dozip" class="submit" value="Take Backup"/>
        <div class="xerox_backup_loader" style="display: none"></div>
      </fieldset>
    </form>
    <fieldset>
      <div class="append_links">

      </div>

    </fieldset>


    <div class="previouslinkns">
      <h2>Old Backups</h2>
      <p>Latest at the top in (Descending Order);</p>
      <?php
      $dir = get_home_path();
      $url = home_url();

      // Sort in descending order
      $files = scandir($dir, 1);

      foreach ($files as $single) {

        if (strpos($single, 'one-click-complete-backups') !== false) {
          ?>
          <div class="backupparent">
            <form action="" method="POST" class="xeroxbackupingdell">

              <fieldset>
                <a href="<?php echo $url . "/" . $single ?>"><?php echo $single ?></a>
                <input type="hidden" id="old_value_dell" value="<?php echo $single ?>">
                <input type="submit" id="hiding" name="dozip" class="submit" value="Remove backup"/>

              </fieldset>
            </form>


          </div>


          <?php

        }


      }

      ?>

    </div>


  </div>
  <div class="xeroxbackup_col-lg-4">
    <fieldset>
      <h1>Pros</h1>

      <ul>
        <li>Manually Create Complete Backup With Database easy for developers and novice users</li>
        <li>No waiting Screen while taking backup just manually click and that's it</li>
        <li>It Automatically works on server side you may leave the page and do your work once
          its completed come back to this page it will show all your backups.
        </li>
        <li></li>
        <li>NO Adds</li>
        <li>Life time Free</li>

      </ul>
    </fieldset>
  </div>
  <script>
    jQuery(document).ready(function () {
      jQuery("#xeroxbackuping").submit(function (e) {
        e.preventDefault(); // avoid to execute the actual submit of the form.
        jQuery(".append_links").empty();
        jQuery(".xerox_backup_loader").show();
        jQuery.ajax({

          type: "POST",
          url: "<?php echo admin_url('admin-ajax.php'); ?>",
          data: {
            action: "oneclickbackups_backup_function",
            dozip: "dozip",
          },
          success: function (data) {
            jQuery(".xerox_backup_loader").hide();
            //alert(data); // show response from the php script.
            jQuery(".append_links").append("<p>" + data + "</p>");
            location.reload();
          },
        });
      });


      jQuery(".xeroxbackupingdell").submit(function (e) {
        e.preventDefault(); // avoid to execute the actual submit of the form.
        var old_val = jQuery("#old_value_dell").val();
        var currnet_item = jQuery(this);

        jQuery.ajax({

          type: "POST",
          url: "<?php echo admin_url('admin-ajax.php'); ?>",
          data: {
            action: "oneclickbackups_backup_function",
            dellbackup: "dellbackup",
            oldval: old_val
          },
          success: function (data) {

            currnet_item.closest(".backupparent").remove()
          },
        });
      });


    });
  </script>

<?php } ?>