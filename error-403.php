<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 Forbidden - ไม่มีสิทธิ์เข้าถึง</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * {
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
        }

        body {
            padding: 0;
            margin: 0;
            font-family: 'Kanit', sans-serif;
        }

        #notfound {
            position: relative;
            height: 100vh;
        }

        #notfound .notfound {
            position: absolute;
            left: 50%;
            top: 50%;
            -webkit-transform: translate(-50%, -50%);
            -ms-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }

        .notfound {
            max-width: 520px;
            width: 100%;
            text-align: center;
            line-height: 1.4;
        }

        .notfound .notfound-403 {
            height: 190px;
        }

        .notfound .notfound-403 h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 146px;
            font-weight: 700;
            margin: 0px;
            color: #232323;
        }

        .notfound .notfound-403 h1>span {
            display: inline-block;
            width: 120px;
            height: 120px;
            background-image: url('/img/forbidden.png');
            background-size: cover;
            -webkit-transform: scale(1.4);
            -ms-transform: scale(1.4);
            transform: scale(1.4);
            z-index: -1;
        }

        .notfound h2 {
            font-family: 'Kanit', sans-serif;
            font-size: 22px;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
            color: #232323;
        }

        .notfound p {
            font-family: 'Kanit', sans-serif;
            color: #787878;
            font-weight: 300;
        }

        .notfound a {
            font-family: 'Kanit', sans-serif;
            display: inline-block;
            padding: 12px 30px;
            font-weight: 700;
            background-color: #f99827;
            color: #fff;
            border-radius: 40px;
            text-decoration: none;
            -webkit-transition: 0.2s all;
            transition: 0.2s all;
        }

        .notfound a:hover {
            opacity: 0.8;
        }

        @media only screen and (max-width: 767px) {
            .notfound .notfound-403 {
                height: 115px;
            }
            .notfound .notfound-403 h1 {
                font-size: 86px;
            }
            .notfound .notfound-403 h1>span {
                width: 86px;
                height: 86px;
            }
        }
    </style>
</head>

<body>
    <div id="notfound">
        <div class="notfound">
            <div class="notfound-403">
                <h1>4<span></span>3</h1>
            </div>
            <h2>ขออภัย! ไม่มีสิทธิ์เข้าถึง</h2>
            <p>คุณไม่มีสิทธิ์ในการเข้าถึงหน้านี้ โปรดติดต่อผู้ดูแลระบบหากคุณเชื่อว่านี่เป็นข้อผิดพลาด</p>
            <a href="/">กลับสู่หน้าหลัก</a>
        </div>
    </div>
</body>

</html>