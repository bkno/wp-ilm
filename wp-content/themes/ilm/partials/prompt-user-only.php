<div class="account-prompt">
    <p>
        Please <a href="/login/?destination=<?= urlencode( $_SERVER['REQUEST_URI'] .'?t='.time() ) ?>"><strong>sign in</strong></a> or <a href="/register/?return=<?= urlencode($_SERVER['REQUEST_URI']) ?>"><strong>create an account</strong></a> to continue.
    </p>
</div>