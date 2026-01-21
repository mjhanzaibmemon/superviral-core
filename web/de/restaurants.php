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
    $tiktok = $_POST['tiktok'];
    $instagram = $_POST['instagram'];
    $Ubereats = $_POST['ubereats'];
    $Justeat = $_POST['justeat'];
    $website = $_POST['website'];
    $id = $_POST['id'];
    $duplicate = $_POST['duplicate'];

    if ($duplicate == "1") {
        mysql_query("UPDATE `de_restaurant` SET `duplicate` = 1 WHERE id = $id");
    }

    mysql_query("UPDATE `de_restaurant` 
        SET `tiktok_profile` = '$tiktok', 
            `instagram_profile` = '$instagram', 
            `ubereats_profile` = '$Ubereats', 
            `justeat_profile` = '$Justeat', 
            `website` = '$website', 
            `done` = 1 
        WHERE id = $id");
}

$query = "SELECT * FROM `de_restaurant` WHERE `done` = 0 LIMIT 1"; 
$result = mysql_query($query);
$data = mysql_fetch_array($result);

$business_name = $data['business_name'];
$address = $data['address'];
$tel = $data['phone'];
$website = $data['website'];
$ubereats_profile = $data['ubereats_profile'];
?>

<div class="container">

    <h1><?php echo $business_name; ?></h1>
    <ul>
        <li><h2>📌 Address: <?php echo $address; ?></h2></li>
        <li><h2>📞 Telephone Number: <?php echo $tel; ?></h2></li>
    </ul>

    <form id="form" method="post" action="">

        <div>
            <label for="tiktok">Tiktok:</label>
            <input type="text" id="tiktok" name="tiktok" required>
            <input type="checkbox" name="tt_null">
            <span>not found</span>
        </div>

        <div>
            <label for="instagram">Instagram:</label>
            <input type="text" id="instagram" name="instagram" required>
            <input type="checkbox" name="ig_null">
            <span>not found</span>
        </div>

        <div>
            <label for="Ubereats">Uber Eats:</label>
            <input type="text" id="Ubereats" name="ubereats" <?php if(!empty($ubereats_profile)) echo "value ='". $ubereats_profile ."' disabled"; ?> required>
            <input type="checkbox" name="ue_null" <?php if(!empty($ubereats_profile)) echo "style='display:none'"; ?>>
            <span>not found</span>
        </div>

        <div>
            <label for="Justeat">JustEat:</label>
            <input type="text" id="Justeat" name="justeat" required>
            <input type="checkbox" name="je_null">
            <span>not found</span>
        </div>
        
        <div>
            <label for="website">Website:</label>
            <input type="text" id="website" name="website" <?php if(!empty($website)) echo "value ='". $website ."' disabled"; ?> required>
            <input type="checkbox" name="web_null" <?php echo $display_web_null ?>>
            <span>not found</span>
        </div>

        <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
        <input type="hidden" id="duplicate" name="duplicate" value="">

        <button type="submit" name="submitBtn" value="1">Submit</button>
        <button id="loadBtn" style="opacity:0.5;"><span class="loader"></span></button>

    </form>

    <button type="button" id="markDupBtn">Mark Duplicate</button>
</div>

<!-- Popup -->
<div id="popupOverlay">
    <div id="popupBox">
        <h3>Search Duplicate</h3>
        <input type="text" id="searchInput" placeholder="Type to search...">
        <div id="searchResults"></div>
        <button id="closePopup">Close</button>
    </div>
</div>

<style>
    body {
        font-family: Arial, sans-serif;
    }
    .container {
        padding: 20px;
        background: white;
    }
    ul {
        list-style: none;
        padding: 0;
        margin: 0 0 20px 0;
    }
    form {
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: center;
    }
    form > div{
        display:flex;
        align-items: center;
        justify-content: start;
        gap:10px;
    }
    label {
        align-self: flex-start;
    }
    input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    button {
        padding: 10px;
        background-color: #4CAF50;
        border: none;
        color: white;
        font-size: 16px;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
    }
    button:hover {
        background-color: #45a049;
    }
    /* Popup styles */
    #popupOverlay {
        display: none;
        position: fixed;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        align-items: center;
        justify-content: center;
    }
    #popupBox {
        background: white;
        padding: 20px;
        border-radius: 10px;
        width: 300px;
        text-align: center;
    }
    #searchResults {
        margin-top: 10px;
        max-height: 150px;
        overflow-y: auto;
        border: 1px solid #ccc;
    }
    #searchResults div {
        padding: 5px;
        cursor: pointer;
    }
    #searchResults div:hover {
        background: #f0f0f0;
    }

    .loader {
    width: 48px;
    height: 48px;
    border: 5px solid #FFF;
    border-bottom-color: transparent;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    animation: rotation 1s linear infinite;
    }

    @keyframes rotation {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    } 
</style>

<script>
document.getElementById("markDupBtn").addEventListener("click", function(){
    document.getElementById("popupOverlay").style.display = "flex";
});

document.getElementById("closePopup").addEventListener("click", function(){
    document.getElementById("popupOverlay").style.display = "none";
});

document.getElementById("searchInput").addEventListener("keyup", function () {
    let q = this.value.trim();
    if (q.length > 1) {
        fetch("search.php?q=" + encodeURIComponent(q))
            .then(res => res.json())
            .then(item => {
                let resultsDiv = document.getElementById("searchResults");
                resultsDiv.innerHTML = "";

                if (item && item.id) {
                    let div = document.createElement("div");
                    div.textContent = item.business_name;
                    div.addEventListener("click", function () {
                        document.getElementById("tiktok").value = item.tiktok_profile || "";
                        document.getElementById("instagram").value = item.instagram_profile || "";
                        document.getElementById("Ubereats").value = item.ubereats_profile || "";
                        document.getElementById("Justeat").value = item.justeat_profile || "";
                        document.getElementById("website").value = item.website || "";
                        document.getElementById("duplicate").value = "1";
                        document.getElementById("popupOverlay").style.display = "none";
                    });
                    resultsDiv.appendChild(div);
                } else {
                    resultsDiv.innerHTML = "<div>No results</div>";
                }
            })
            .catch(err => console.error("Search error:", err));
    }
});

</script>
