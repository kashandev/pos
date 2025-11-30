<header class="main-header">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button" id="button-menu">
        <i class="fa fa-indent fa-lg"></i>
    </a>

    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">
        <?php if($company_logo): ?>
        <img src="<?php echo $company_logo;?>" alt="Logo" />
        <?php endif; ?>
        <!-- Navbar Right Menu -->
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <li class="hidden-sm">
                    <a href="javascript:void();">Session Time: <span id="session_time"><?php echo date('Y-m-d H:i:s'); ?></span></a>
                </li>
                <li class="hidden-sm">
                    <a href="javascript:void();"><?php echo $branch_name; ?></a>
                </li>
                <li class="hidden-sm">
                    <a href="javascript:void();"><?php echo $fiscal_year; ?></a>
                </li>
                <!-- User Account: style can be found in dropdown.less > -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?php echo $user_image_50_50; ?>" class="user-image" alt="User Image">
                        <span class="hidden-xs"><?php echo $user_name; ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image > -->
                        <li class="user-header">
                            <img src="<?php echo $user_image_160_160; ?>" class="img-circle" alt="User Image">
                            <p>
                                <?php echo $user_name; ?>
                            </p>
                        </li>
                        <!-- Menu Footer > -->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="<?php echo $href_user_profile; ?>" class="btn btn-default btn-flat"><?php echo $lang['profile']; ?></a>
                            </div>
                            <div class="pull-right">
                                <a href="<?php echo $href_logout; ?>" class="btn btn-default btn-flat"><?php echo $lang['sign_out']; ?></a>
                            </div>
                        </li>
                    </ul>
                </li>
                <!-- Control Sidebar Toggle Button > -->
                <li>
                    <a href="#" data-toggle="control-sidebar" style="display: none;"><i class="fa fa-gears"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>
