<style>
#left-sidebar {
    background-image: url("assets/images/logo_bg.png");
    background-repeat: no-repeat;
    background-size: 200px auto;
    /*background-position: left bottom;*/
    background-position: -6vh 69vh;
    
}
</style>
<?php


echo"

<div id=\"left-sidebar\" class=\"sidebar\">
    <div>
        <div class=\"user-account\">
            <img src=\"assets/images/placeholder.jpg\" class=\"rounded-circle user-photo\" alt=\"User Profile Picture\">
            <div class=\"dropdown\">
                <span>Welcome,</span>
                <strong><p class=\"user-name\" data-bs-toggle=\"dropdown\" id = \"this-username\">User 1</p></strong>
            </div>
            <hr>
        </div>
        
        <!-- Nav tabs -->
        <ul class=\"nav nav-tabs\">
            <li class=\"nav-item\"><a class=\"nav-link show active\" data-bs-toggle=\"tab\" href=\"#menu\">Menu</a></li>
            <li class=\"nav-item\"><a class=\"nav-link\" data-bs-toggle=\"tab\" href=\"#setting\"><i class=\"icon-settings\"></i></a></li>
        </ul>
            
        <!-- Tab panes -->
        <div class=\"tab-content ps-0 pe-0\">
            <div class=\"tab-pane active\" id=\"menu\">
                <nav id=\"left-sidebar-nav\" class=\"sidebar-nav\">
                    <ul id=\"main-menu\" class=\"metismenu\">
                        <li>
                            <a href=\"dashboard.php\"><i class=\"icon-home\"></i> <span>Dashboard</span></a>
                        </li>
                        <li>
                            <a class=\"has-arrow\"><i class=\"icon-book-open\"></i> <span>Reports</span></a>
                            <ul>
                                <li><a href=\"plant.php\">Plant</a></li>
                                <li><a href=\"query.php\">Query</a></li>
                            </ul>
                        </li>
                        <li>
                            <a class=\"has-arrow\"><i class=\"icon-screen-desktop\"></i> <span>Site Management</span></a>
                            <ul>                                    
                                <li><a href=\"site.php\">Site</a></li>
                                
                            </ul>
                        </li>
                        <li class=\"\">
                            <a class=\"has-arrow\"><i class=\"icon-user\"></i> <span>User Management</span></a>
                            <ul>                                    
                                <li><a href='user-management.php'>Users</a></li>
                            </ul>
                        </li> 
                        
                        <li>
                            <a href=\"archive.php\"><i class=\"fa fa-file-archive-o\"></i> <span>Archive</span></a>
                        </li> 
                    
                    </ul>
                </nav>
            </div>

            <div class=\"tab-pane px-2\" id=\"setting\">
                <h6>Choose Skin</h6>
                <ul class=\"choose-skin list-unstyled\">
                    <li data-theme=\"purple\">
                        <div class=\"purple\"></div>
                        <span>Purple</span>
                    </li>                   
                    <li data-theme=\"blue\">
                        <div class=\"blue\"></div>
                        <span>Blue</span>
                    </li>
                    <li data-theme=\"cyan\" class=\"active\">
                        <div class=\"cyan\"></div>
                        <span>Cyan</span>
                    </li>
                    <li data-theme=\"green\">
                        <div class=\"green\"></div>
                        <span>Green</span>
                    </li>
                    <li data-theme=\"orange\">
                        <div class=\"orange\"></div>
                        <span>Orange</span>
                    </li>
                    <li data-theme=\"blush\">
                        <div class=\"blush\"></div>
                        <span>Blush</span>
                    </li>
                </ul>
                <hr>
                <div class=\"setting-mode mb-3\">
                    <ul class=\"list-group list-unstyled mb-0 mt-1\">
                        <li class=\"list-group-item d-flex align-items-center py-1 px-2\">
                            <div class=\"form-check form-switch theme-switch mb-0\">
                                <input class=\"form-check-input\" type=\"checkbox\" id=\"theme-switch\">
                                <label class=\"form-check-label\" for=\"theme-switch\">Enable Dark Mode!</label>
                            </div>
                            </li>
                        <li class=\"list-group-item d-flex align-items-center py-1 px-2\">
                            <div class=\"form-check form-switch theme-high-contrast mb-0\">
                                <input class=\"form-check-input\" type=\"checkbox\" id=\"theme-high-contrast\">
                                <label class=\"form-check-label\" for=\"theme-high-contrast\">Enable High Contrast</label>
                            </div>
                        </li>
                        <li class=\"list-group-item d-flex align-items-center py-1 px-2\">
                            <div class=\"form-check form-switch theme-rtl mb-0\">
                                <input class=\"form-check-input\" type=\"checkbox\" id=\"theme-rtl\">
                                <label class=\"form-check-label\" for=\"theme-rtl\">Enable RTL Mode!</label>
                            </div>
                        </li>
                    </ul>
                </div>
                <hr>
            </div>                
        </div>          
    </div>
</div>

<script>
        
    document.getElementById('this-username').innerHTML = '" . $_SESSION['name'] . "';
        
</script>

";
?>

<!--<div class=\"justify-content-center\" style=\"display:flex;justify-content:center;\">-->
<!--    <img class=\"main-logo\" src=\"assets/images/logo_bg.png\" style=\"width:200px;height:auto;text-align:center;position:absolute;bottom: -49px;left: -13px;\" alt=\"\" />                 -->
<!--</div>-->