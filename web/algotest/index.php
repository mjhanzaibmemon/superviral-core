<?php
$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];
$initial = $subdomain . '.';
$subdomain = '/' . $subdomain . '/foodie.app';
if (!empty($initial) && $initial != "foodie.") {
    $_SERVER['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'] . $subdomain;
}

require_once dirname($_SERVER["DOCUMENT_ROOT"]) . '/foodie.app/config/config.php';

if (isset($_POST['submitBtn']) && $_POST['submitBtn'] == '1') {
 
}

?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Algo UI TEST</title>
<style>
  :root{
    --border: 3px;           /* thickness of all borders */
    --radius: 6px;           /* rounded corners */
    --gap: 38px;             /* space between fields */
    --field-w: 180px;        /* width of each input box */
    --field-h: 40px;         /* height of each input box */
    --font: system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
  }

  * { box-sizing: border-box; }

  body{
    margin: 0;
    padding: 24px;
    font-family: var(--font);
    background: #fff;
    color: #000;
  }

  /* Outer framed panel */
  .panel{
    border: var(--border) solid #000;
    border-radius: var(--radius);
    padding: 28px 26px 26px;
    position: relative;
    max-width: 1115px;
    margin: 0 auto;
  }

  /* Title like in screenshot */
  .panel__title{
    position: absolute;
    top: -0.95rem;
    left: 16px;
    background: #fff;
    padding: 0 8px;
    font-weight: 700;
    font-size: 16px;
  }

  /* Row of fields */
  .fields{
    display: flex;
    align-items: flex-start;
    gap: var(--gap);
    flex-wrap: wrap;
  }

  .field{
    display: grid;
    grid-template-rows: auto 1fr;
    width: var(--field-w);
  }

  .field label{
    font-size: 14px;
    font-weight: 700;
    margin: 0 0 6px 2px;
    user-select: none;
  }

  /* Styled input boxes */
  .box-input{
    height: var(--field-h);
    width: 100%;
    border: var(--border) solid #000;
    border-radius: var(--radius);
    padding: 8px 10px;
    font-size: 14px;
    outline: none;
    background: #fff;
  }

  .box-input:focus{
    box-shadow: 0 0 0 2px #00000040;
  }
</style>
</head>
<body>

  <section class="panel">
    <div class="panel__title">Test variables</div>

    <div class="fields">
      <div class="field">
        <label for="dopamine_score">dopamine_score</label>
        <input id="dopamine_score" class="box-input" type="text">
      </div>

      <div class="field">
        <label for="user_embedding">user_embedding</label>
        <input id="user_embedding" class="box-input" type="text">
      </div>

      <div class="field">
        <label for="location">location</label>
        <input id="location" class="box-input" type="text">
      </div>

      <div class="field">
        <label for="time">time</label>
        <input id="time" class="box-input" type="text">
      </div>

      <div class="field">
        <label for="account_id">account_id</label>
        <input id="account_id" class="box-input" type="text">
      </div>
    </div>
  </section>

</body>
</html>
