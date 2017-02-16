<?php
/**
 * Created by PhpStorm.
 * User: jonn
 * Date: 12/02/2017
 * Time: 14:25
 */
global $sitemap;
?>

<h2>Verify Your Account</h2>

<form action="/post.php" method="post">

    <label class="sw-c-input__label" for="user--verification">Verification Code *</label>
    <input class="sw-c-input__item sw-js-validation" type="text" id="user--verification" name="user--verification" value="<?php echo $sitemap->hash;?>" required/>

    <input type="hidden" name="post--do" value="user--login--verify"/>
    <input type="hidden" name="post--return" value="user"/>

    <input class="sw-c-submit sw-js-submit sw-is-clickable" type="submit" value="VERIFY"/>

</form>