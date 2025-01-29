<div class="wrap sxth-digests-login">
   <?php if (!isset($_GET['step'])): ?>
      <h1>Login to SXTH Digests</h1>
      <form method="post">
         <?php wp_nonce_field('sxth-digests-login'); ?>
         <input type="hidden" name="sxth_digests_login" value="1">

         <p>
            <label>Email Address</label>
            <input type="email" name="email" required>
         </p>

         <button type="submit" class="button button-primary">
            Send Verification Code
         </button>
      </form>
   <?php else: ?>
      <h1>Enter Verification Token</h1>
      <form method="post">
         <?php wp_nonce_field('sxth-digests-verify'); ?>
         <input type="hidden" name="sxth_digests_verify" value="1">

         <p>
            <label>Verification Token</label>
            <input type="text" name="token" required>
         </p>

         <button type="submit" class="button button-primary">
            Verify Token
         </button>
      </form>
   <?php endif; ?>
</div>