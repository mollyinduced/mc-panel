<?php
session_start();



?>

<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Molly Panel</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/logo.gif">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" />
    </head>

    <body>
        <div class="container">
            

        <aside>

            <div class="top">
                <div class="logo">
                    <h2 id="brand">Home - Molly Panel</>
                </div>
            </div>

            <div class="sidebar">
                <a href="" class="active">
                    <span class="material-symbols-rounded">grid_view</span>
                        <h3>Home</h3>
                </a>
            </div>

        </aside>

        <main>

    <h1>Categories</h1>

    <div class="categories">
        <!-- First Category -->
        <div class="category">
            <div class="category-title">General</div>
            <div class="category-content">
                <ul>
                    <li><a href="#">Introduction</a></li>
                    <li><a href="#">Rules</a></li>
                    <li><a href="#">Announcements</a></li>
                </ul>
            </div>
        </div>

        <!-- Second Category -->
        <div class="category">
            <div class="category-title">Development</div>
            <div class="category-content">
                <ul>
                    <li><a href="#">Coding Help</a></li>
                    <li><a href="#">Project Showcase</a></li>
                    <li><a href="#">Resources</a></li>
                </ul>
            </div>
        </div>

        <!-- Third Category -->
        <div class="category">
            <div class="category-title">Support</div>
            <div class="category-content">
                <ul>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Report Issues</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
        </div>
    </div>

        <div class="item-list">
            <div class="button">
                <h3>Create Server</h3>
            </div>
            <div class="button">
            <h3>Upload your own server</h3>
        </div>
        </main>


        </div>
    </body>
</html>