<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MainPage</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
        }

        .header {
            background-color: #007BFF;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 2.5em;
        }

        .container {
            width: 100%;
            /* height: 100vh; */
            display: flex;
            flex-direction: row;
            align-items: top;
            margin: 20px auto;
            max-width: 1800px;
            text-align: center;
        }

        .button-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            width: 100%;
            padding: 20px;
        }

        .button {
            padding: 15px 20px;
            font-size: 1.2em;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .button:hover {
            background-color: #0056b3;
            color: white;
            transform: scale(1.05);
        }

        .select-database {
            background-color: #28A745;
            color: white;
        }

        .connect-database {
            background-color: #FFC107;
            color: #333;
        }

        .upload-file {
            background-color: #17A2B8;
            color: white;
        }

        .analyze-database {
            background-color: #6F42C1;
            color: white;
        }

        .get- {
            background-color: #DC3545;
            color: white;
        }

        .search-dependencies {
            background-color: #343A40;
            color: white;
        }

        .download- {
            background-color: yellow;
            color: black;
        }

        footer {
            bottom: 0;
            margin-top: 30px;
            background-color: #007BFF;
            color: white;
            padding: 10px 0;
            text-align: center;
        }

        .dropdown {
            display: none;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            width: 250px;
        }

        .actionable-item {
            margin-top: 100px;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 50px;
            display: none;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
            width: 100%;
            height: 100%;
        }

        .dropdown ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .dropdown ul li {
            padding: 8px 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .dropdown ul li:hover {
            background-color: #f1f1f1;
        }

        .side-panel {

            margin: 10px;
            padding-top: 20px;
            width: 20%;
            height: 100vh;
            background-color: #e6e6e6;
            border-radius: 20px;
        }

        .main-panel {
            width: 80%;
            /* height: 100vh; */
            /* margin: 10px; */
            background-color: #e6e6e6;
            border-radius: 20px;
        }

        * {
            box-sizing: border-box;
        }

        .actionable-panel {
            padding-top: 25px;
            margin-top: 25px;
            margin-left: auto;
            margin-right: auto;
            border-radius: 20px;
            background-color: #f7f7f7;
            /* width: 97%; */
            /* min-height: 65%; */
        }

        .setConfig {
            color: black;
        }

        .connectDb-form {
            display: flex;
            /* justify-content: left;
            align-items: center; */
            flex-direction: row;
            flex-wrap: wrap;


        }

        .connectDb-form div {
            width: 100%;
            margin: 10px;
        }

        .spinner {
            width: 200px;
            height: 200px;
            border: 5px solid #ccc;
            border-top: 5px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            /* position: relative; */
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .analyzeDatabase {
            /* display: flex; */
            justify-content: center;
            align-items: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f1f9ff;
        }

        caption {
            margin-bottom: 10px;
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>

<body>

    <header class="header">
        <h1>FDA Dashboard</h1>
    </header>


    <div class="container">

        <div class="side-panel">
            Info

            <p id="selectedDatabase"> Selected database: none</p>
            <p id="selectedAlg"> Selected algorithm: none</p>
        </div>

        <div class="main-panel">
            <h2>Welcome to the MainPage</h2>
            <p>Select an action below to get started:</p>

            <div class="button-grid">
                <button class="button select-database" onclick="toggleDropdown()">Select Database</button>
                <button class="button connect-database" onclick="connectDb()">Set Database Connection</button>
                <button class="button upload-file" onclick="uploadDatabase()">Upload File for Database</button>
                <button class="button analyze-database" onclick="analyzeDatabase()">Analyze Database</button>
                <button class="button get-results" onclick="getResults()">Get Results</button>
                <button class="button search-dependencies" onclick="search()">Search Dependencies</button>
                <button class="button download-results" onclick="download()">Download results</button>
                <button class="button set-config" onclick="setConfig()">Set configs</button>
            </div>

            <div class="actionable-panel">

                <div class="dropdown actionable-item" id="databaseDropdown">
                    <p>Select database: </p>
                    <ul>
                        @foreach ($databases as $database)
                        <li class="dropdown-item" onclick="selectDatabase(this)">{{ $database }}</li>
                        @endforeach
                    </ul>
                </div>

                <div class="dropdown setConfigPanel actionable-item" id="setConfig">

                    <p>Choose and set configs</p>

                    <p>Set algorithm:</p>

                    <div class="">
                        <ul>
                            @foreach ($algs as $alg)
                            <li class="dropdown-item" onclick="selectAlg(this)">{{ $alg }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="dropdown connectDb actionable-item" id="connectDb">

                    <p>Fill in the form to save the settings and connect to the database server:</p>
                    <form class="connectDb-form">

                        <p>Database</p>

                        <div>
                            <label for="connection_name">connection_name: </label>
                            <input id="connection_name" type="text">
                        </div>

                        <div>
                            <label for="driver">driver: </label>
                            <input id="driver" type="text">
                        </div>

                        <div>
                            <label for="host">host: </label>
                            <input id="host" type="text">
                        </div>

                        <div>
                            <label for="port">port: </label>
                            <input id="port" type="number">
                        </div>

                        <div>
                            <label for="name">name: </label>
                            <input id="name" type="text">
                        </div>

                        <p>User</p>

                        <div>
                            <label for="username">username: </label>
                            <input id="username" type="text">
                        </div>

                        <div>
                            <label for="password">password: </label>
                            <input id="password" type="text">
                        </div>


                        <div>
                            <input id="button-add-database" title="Add" type="button">
                        </div>

                    </form>
                </div>

                <div class="dropdown actionable-item" id="uploadDatabase">
                    <p>Select a file in CSV format to load the database:</p>
                    <form action="">
                        <label for="file">Choose file: </label>
                        <input id="file" type="file">
                        <input type="button">
                    </form>
                </div>
                <div class="dropdown actionable-item analyzeDatabase" id="analyzeDatabase">
                    <div class="spinner"></div>

                </div>
                <div class="dropdown actionable-item" id="results">
                    get results

                    <table>
                        <caption>Data Overview</caption>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Column Count</th>
                                <th>Dead Rows Estimate</th>
                                <th>Foreign Key Count</th>
                                <th>Index Scans</th>
                                <th>Live Rows Estimate</th>
                                <th>Primary Key Count</th>
                                <th>Schema Name</th>
                                <th>Sequential Scans</th>
                                <th>Table Name</th>
                                <th>Table Size (Bytes)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Пример данных, которые замените на ваши -->
                            <tr>
                                <td>1</td>
                                <td>5</td>
                                <td>100</td>
                                <td>3</td>
                                <td>50</td>
                                <td>200</td>
                                <td>1</td>
                                <td>public</td>
                                <td>20</td>
                                <td>users</td>
                                <td>204800</td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>8</td>
                                <td>50</td>
                                <td>1</td>
                                <td>10</td>
                                <td>150</td>
                                <td>1</td>
                                <td>public</td>
                                <td>15</td>
                                <td>orders</td>
                                <td>102400</td>
                            </tr>
                        </tbody>
                    </table>

                </div>
                <div class="dropdown actionable-item" id="search">
                    search

                </div>
                <div class="dropdown actionable-item" id="download">
                    download

                </div>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2024 Database Management System. All Rights Reserved.
    </footer>

    <script>
        function toggleDropdown() {

            const setConfig = document.getElementById('setConfig');

            if (setConfig.style.display !== 'none') {
                setConfig.style.display = 'none';
            }


            const dropdown = document.getElementById('databaseDropdown');
            if (dropdown.style.display === 'none' || dropdown.style.display === '') {
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        }
    </script>

    <script>
        function setConfig() {

            const databaseDropdown = document.getElementById('databaseDropdown');

            if (databaseDropdown.style.display !== 'none') {
                databaseDropdown.style.display = 'none';
            }

            const setConfig = document.getElementById('setConfig');
            if (setConfig.style.display === 'none' || setConfig.style.display === '') {
                setConfig.style.display = 'block';
            } else {
                setConfig.style.display = 'none';
            }
        }
    </script>

    <script>
        function connectDb() {

            const connectDb = document.getElementById('connectDb');
            if (connectDb.style.display === 'none' || connectDb.style.display === '') {
                connectDb.style.display = 'block';
            } else {
                connectDb.style.display = 'none';
            }
        }
    </script>

    <script>
        function uploadDatabase() {

            const element = document.getElementById('uploadDatabase');
            if (element.style.display === 'none' || element.style.display === '') {
                element.style.display = 'block';
            } else {
                element.style.display = 'none';
            }
        }
    </script>

    <script>
        function analyzeDatabase() {

            const element = document.getElementById('analyzeDatabase');
            if (element.style.display === 'none' || element.style.display === '') {
                element.style.display = 'block';
            } else {
                element.style.display = 'none';
            }
        }
    </script>

    <script>
        async function fillInData() {
            const result = []
            const url = "http://localhost/analyze";

            try {
                const response = await (await fetch(url, {
                    method: "GET"
                })).json()

                console.log(response)

                const targetElement = document.querySelector('tbody');

                const result2 = response.map((element, index) => {
                    const row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${element.column_count}</td>
                            <td>${element.dead_rows_estimate}</td>
                            <td>${element.foreign_key_count}</td>
                            <td>${element.index_scans}</td>
                            <td>${element.live_rows_estimate}</td>
                            <td>${element.primary_key_count}</td>
                            <td>${element.schema_name}</td>
                            <td>${element.sequential_scans}</td>
                            <td>${element.table_name}</td>
                            <td>${element.table_size_bytes}</td>
                        </tr>
                    `;

                    targetElement.innerHTML += row;
                })

                console.log(result2);


                targetElement.insertAdjacentHTML('afterend', result2);





            } catch (error) {
                console.error(error.message);
            }
        }

        fillInData()

        function getResults() {

            const element = document.getElementById('results');
            if (element.style.display === 'none' || element.style.display === '') {
                element.style.display = 'block';
            } else {
                element.style.display = 'none';
            }
        }
    </script>

    <script>
        function search() {

            const element = document.getElementById('search');
            if (element.style.display === 'none' || element.style.display === '') {
                element.style.display = 'block';
            } else {
                element.style.display = 'none';
            }
        }
    </script>

    <script>
        function download() {

            const element = document.getElementById('download');
            if (element.style.display === 'none' || element.style.display === '') {
                element.style.display = 'block';
            } else {
                element.style.display = 'none';
            }
        }
    </script>



    <!-- -------------------------------------------------------------------------------------------------- -->
    <script>
        function selectDatabase(element) {

            const text = element.textContent;
            const output = document.getElementById('selectedDatabase');
            output.textContent = `Selected database: ${text}`;
        }
    </script>

    <script>
        function selectAlg(element) {

            const text = element.textContent;
            const output = document.getElementById('selectedAlg');
            output.textContent = `Selected algorithm: ${text}`;
        }
    </script>

    <!-- -------------------------------------------------------------------------------------------------- -->

</body>

</html>