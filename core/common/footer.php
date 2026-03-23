<style>

#footer-sec {
    background-color: #70AD47;
    padding: 20px 50px;
    color: #fff;
    font-size: 15px;
    text-align: center;
}

</style>

<?php

echo"

<div id=\"footer-sec\">
    © Copyright <span id=\"year\"></span> | Designed and Developed by Ecoasis Technical Services Ltd.
</div>

";
?>

<!-- To get year -->
<script>
var currentDate = new Date();
var year = currentDate.getFullYear();

document.getElementById("year").innerHTML = year;

</script>