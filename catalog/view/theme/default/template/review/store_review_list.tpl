<?php if ($reviews) { ?>
    <div class="row">
  <?php foreach ($reviews as $review) { ?>

    <div class="col-md-12 review-item ">


        <div class="col-xs-6 col-sm-6">  <?php
          if(isset($review['link'])&& !empty($review['link'])){ ?>
            <strong><a href="<?php echo $review['link']; ?>"><?php echo $review['author']; ?></a></strong>
          <?php }
          else {?>
            <strong><?php echo $review['author']; ?></strong>

          <?php } ?>
          <div style="padding-bottom:10px ">
            <div class="sel-items">


            <?php for ($i = 1; $i <= 5; $i++) { ?>
              <?php if ($review['rating'] >= $i) { ?>
            <div class="star-item item-1 "  data-item="1"></div>

              <?php }  ?>


            <?php } ?>
            </div>

          </div>
        </div>
        <div class="col-xs-6 col-sm-6 text-right">
          <?php echo $review['date_added']; ?>

        </div>

      <div class=" col-xs-12">
        <div class="review"><?php echo $review['text']; ?></div>
        <?php
        if(isset($review['admin_answer'])&& !empty($review['admin_answer'])){ ?>
          <div class="admin-answer">
            <p><strong>Administrator</strong></p>
            <?php echo $review['admin_answer']; ?>
          </div>
        <?php }?>


      </div>




    </div>

  <?php } ?>


    </div>


<div class="row">
  <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
  <div class="col-sm-6 text-right"><?php echo $results; ?></div>
</div>
<?php } else { ?>
<p><?php echo $text_no_reviews; ?></p>
<?php } ?>
