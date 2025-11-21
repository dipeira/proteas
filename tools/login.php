<?php
    function show_login($log, $failed = false){
    ?>
        <html>
            <head>
                <?php 
                $root_path = '../';
                $page_title = 'Είσοδος - Πρωτέας';
                require '../etc/head.php'; 
                ?>
            </head>
            <body class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
                <div class="w-full max-w-md space-y-8">
                    <!-- Logo and Title -->
                    <div class="text-center">
                        <div class="flex justify-center mb-6">
                            <img src="../images/logo.png" alt="Πρωτέας" class="h-20 w-auto">
                        </div>
                        <h2 class="text-3xl font-bold text-gray-900">Πρωτέας</h2>
                        <p class="mt-2 text-sm text-gray-600">Πληροφοριακό σύστημα προσωπικού</p>
                        <p class="mt-1 text-xs text-gray-500">Διεύθυνση Πρωτοβάθμιας Εκπαίδευσης Ηρακλείου</p>
                    </div>

                    <!-- Login Form Card -->
                    <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
                        <?php if ($failed): ?>
                            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Η είσοδος απέτυχε</h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <p>Παρακαλώ δοκιμάστε με έναν έγκυρο συνδυασμό ονόματος χρήστη - κωδικού.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form name="login" method="post" id="login" class="mt-8 space-y-6" action="">
                            <div class="space-y-4">
                                <div>
                                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                        Όνομα Χρήστη
                                    </label>
                                    <input 
                                        id="username" 
                                        name="username" 
                                        type="text" 
                                        autocomplete="username" 
                                        required 
                                        class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm transition-colors"
                                        placeholder="Εισάγετε όνομα χρήστη"
                                    >
                                </div>
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                        Κωδικός
                                    </label>
                                    <input 
                                        id="password" 
                                        name="password" 
                                        type="password" 
                                        autocomplete="current-password" 
                                        required 
                                        class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm transition-colors"
                                        placeholder="Εισάγετε κωδικό"
                                    >
                                </div>
                            </div>

                            <input name="action" id="action" value="login" type="hidden">

                            <div>
                                <button 
                                    type="submit" 
                                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                                >
                                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                        <svg class="h-5 w-5 text-emerald-300 group-hover:text-emerald-200" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    Είσοδος στο σύστημα
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="text-center text-xs text-gray-500">
                        <p>&copy; <?php echo date('Y'); ?> Διεύθυνση Πρωτοβάθμιας Εκπαίδευσης Ηρακλείου</p>
                    </div>
                </div>
            </body>
        </html>
    <?php
    }
    header('Content-type: text/html; charset=utf-8'); 
    require_once("../config.php");
    include("class.login.php");
    $log = new logmein();     //Instentiate the class
    $log->dbconnect();        //Connect to the database

    if ($_GET['logout'])
    {
        $log->logout();
        header("Location: login.php");
    }

    if (!isset($_REQUEST['action']))
    {
        show_login($log);
    }

    if($_REQUEST['action'] == "login"){
        if($log->login("logon", $_REQUEST['username'], $_REQUEST['password']) == true)
        {        
          header("Location: ../index.php");
        }
        else {
            show_login($log, true);
        }
    }
?>    
