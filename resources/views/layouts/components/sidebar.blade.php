<div class="leftside-menu">

    <!-- Brand Logo Light -->
    @include('layouts.components.sidebar-brand_logo')

    <!-- Sidebar Hover Menu Toggle Button -->
    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
        <i class="ri-checkbox-blank-circle-line align-middle"></i>
    </div>

    <!-- Full Sidebar Menu Close Button -->
    <div class="button-close-fullsidebar">
        <i class="ri-close-fill align-middle"></i>
    </div>

    <!-- Sidebar -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <!-- Leftbar User -->
        <div class="leftbar-user">
            <a href="pages-profile.html">
                <img src="assets/images/users/avatar-1.jpg" alt="user-image" height="42"
                    class="rounded-circle shadow-sm">
                <span class="leftbar-user-name mt-2">{{ Auth::user()->name }}</span>
            </a>
        </div>

        <!--- Sidemenu -->

        <div class="leftside-menu">

            <!-- Brand Logo -->
            <a href="#" class="logo logo-light">
                <span class="logo-lg">
                    <img src="assets/images/logo.png" alt="logo">
                </span>
                <span class="logo-sm">
                    <img src="assets/images/logo-sm.png" alt="small logo">
                </span>
            </a>

            <a href="#" class="logo logo-dark">
                <span class="logo-lg">
                    <img src="assets/images/logo-dark.png" alt="dark logo">
                </span>
                <span class="logo-sm">
                    <img src="assets/images/logo-dark-sm.png" alt="small logo">
                </span>
            </a>

            <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
                <i class="ri-checkbox-blank-circle-line align-middle"></i>
            </div>

            <div class="button-close-fullsidebar">
                <i class="ri-close-fill align-middle"></i>
            </div>

            <!-- Sidebar -->
            <div class="h-100" id="leftside-menu-container" data-simplebar>
                <!-- Leftbar User -->
                <div class="leftbar-user">
                    <a href="#">
                        <img src="assets/images/users/avatar-1.jpg" alt="user-image" height="42"
                            class="rounded-circle shadow-sm">
                        <span class="leftbar-user-name mt-2">{{ Auth::user()->name }}</span>
                    </a>
                </div>

                <!--- Sidemenu -->
                <ul class="side-nav">

                    <li class="side-nav-title">Navigation</li>

                    <li class="side-nav-item">
                        <a href="/dashboard" class="side-nav-link">
                            <i class="uil-home-alt"></i>
                            <span> Dashboard </span>
                        </a>
                    </li>

                    <li class="side-nav-title">Learning Management</li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarUsers" aria-expanded="false"
                            aria-controls="sidebarUsers" class="side-nav-link">
                            <i class="uil-users-alt"></i>
                            <span> Users </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarUsers">
                            <ul class="side-nav-second-level">
                                <li><a href="/student">Students</a></li>
                                <li><a href="/teacher">Teachers</a></li>
                                <li><a href="/staff">Staffs</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarClasses" aria-expanded="false"
                            aria-controls="sidebarClasses" class="side-nav-link">
                            <i class="uil-presentation"></i>
                            <span> Classes </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarClasses">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="/class">Manage Classes</a>
                                </li>
                                <li>
                                    <a href="/class-student">Assign Students</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a href="/class-management" class="side-nav-link">
                            <i class="uil-books"></i>
                            <span> Class Management </span>
                        </a>
                    </li>

                    <li class="side-nav-item">
                        <a href="/class-subject-assignment" class="side-nav-link">
                            <i class="uil-books"></i>
                            <span> Class Subject Management </span>
                        </a>
                    </li>

                    <li class="side-nav-item">
                        <a href="/subject" class="side-nav-link">
                            <i class="uil-books"></i>
                            <span> Subjects </span>
                        </a>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarEducational" aria-expanded="false"
                            aria-controls="sidebarEducational" class="side-nav-link">
                            <i class="uil-graduation-cap"></i>
                            <span> Educational </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarEducational">
                            <ul class="side-nav-second-level">
                                <li><a href="/educational-level">Educational Levels</a></li>
                                <li><a href="/major">Majors</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a href="/learning-material" class="side-nav-link">
                            <i class="uil-file-info-alt"></i>
                            <span> Learning Materials </span>
                        </a>
                    </li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarAssignments" aria-expanded="false"
                            aria-controls="sidebarAssignments" class="side-nav-link">
                            <i class="uil-edit-alt"></i>
                            <span> Assignments & Submission</span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarAssignments">
                            <ul class="side-nav-second-level">
                                <li><a href="/assignment"> Assignment</a></li>
                                <li><a href="/assignment-submission"> Submissions</a></li>
                            </ul>
                        </div>
                    </li>

                    <li class="side-nav-item">
                        <a href="/teacher-assignment" class="side-nav-link">
                            <i class="uil-clipboard-alt"></i>
                            <span> Teacher Assignment </span>
                        </a>
                    </li>

                    <li class="side-nav-item">
                        <a href="/student-assignment" class="side-nav-link">
                            <i class="uil-clipboard-alt"></i>
                            <span> Student Assignment </span>
                        </a>
                    </li>

                    <li class="side-nav-item">
                        <a href="#" class="side-nav-link">
                            <i class="uil-clipboard-alt"></i>
                            <span> Exams </span>
                        </a>
                    </li>

                    <li class="side-nav-item">
                        <a href="#" class="side-nav-link">
                            <i class="uil-chart-bar"></i>
                            <span> Reports </span>
                        </a>
                    </li>

                    <li class="side-nav-title">System Settings</li>

                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarSettings" aria-expanded="false"
                            aria-controls="sidebarSettings" class="side-nav-link">
                            <i class="uil-cog"></i>
                            <span> Settings </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarSettings">
                            <ul class="side-nav-second-level">
                                <li><a href="/sidebar-management">User Management</a></li>
                                <li><a href="/module-management">Module Management</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
                <!--- End Sidemenu -->

                <div class="clearfix"></div>
            </div>
        </div>

        <!--- End Sidemenu -->

        <div class="clearfix"></div>
    </div>
</div>
