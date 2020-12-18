<?php include "config.php"; ?>
<a class="site-name" href="<?php if ($l) { echo '/home'; } else { echo '/'; } ?>">LearnChess<?php if($devMode) {echo " dev"; } else {echo ".xyz";} ?></a>
<div class="right">
    <a href="/contact">Contact</a>
    <span class="responsive"> •
        <a href="/about">About</a> •
        <a href="/thanks">Thanks</a>
    </span>
</div>
<?php if ($devMode) {?>
<div class="dev">
    <i class="fa fa-eye"></i>
    <input type="checkbox" id="dev-theme-switch">
    <label class="dev-switch" for="dev-theme-switch">
        <span class="dev-inner"></span>
    </label>
</div>
<script src="/js/dev.js"></script>
<?php } ?>
