<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin Login - License Management</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/AdminLTE.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/css/skins/_all-skins.min.css">
    <style>
        .login-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .login-box {
            margin: 7% auto;
        }
        .login-box-body {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .login-logo {
            color: white;
            font-weight: 300;
            margin-bottom: 20px;
        }
        .login-logo a {
            color: white;
            text-decoration: none;
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .form-control {
            border-radius: 25px;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .input-group-addon {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            border-radius: 25px 0 0 25px;
        }
        .admin-badge {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .license-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            color: white;
            text-align: center;
        }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="login-logo">
        <a href="#"><b>License</b> Management</a>
        <div class="admin-badge">
            <i class="fas fa-shield-alt"></i> Administrator Access
        </div>
    </div>

    <div class="login-box-body">
        <p class="login-box-msg">
            <strong>Admin Portal Access</strong><br>
            <small class="text-muted">Enter your administrator credentials</small>
        </p>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <i class="icon fa fa-ban"></i> {{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <i class="icon fa fa-check"></i> {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="post">
            @csrf
            
            <div class="form-group has-feedback">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fas fa-user-shield"></i>
                    </span>
                    <input type="text" 
                           class="form-control @error('username') is-invalid @enderror" 
                           placeholder="Administrator Username"
                           name="username" 
                           value="{{ old('username') }}"
                           required 
                           autofocus>
                </div>
                @error('username')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group has-feedback">
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Administrator Password"
                           name="password" 
                           required>
                </div>
                @error('password')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <div class="row">
                <div class="col-xs-8">
                    <div class="checkbox icheck">
                        <label>
                            <input type="checkbox" name="remember"> Remember Me
                        </label>
                    </div>
                </div>
                <div class="col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">
                        <i class="fas fa-sign-in-alt"></i> Access Portal
                    </button>
                </div>
            </div>
        </form>

        <div class="text-center" style="margin-top: 20px;">
            <small class="text-muted">
                <i class="fas fa-info-circle"></i> 
                This is a restricted area for license administrators only
            </small>
        </div>
    </div>

    <div class="license-info">
        <h4><i class="fas fa-key"></i> License Management Portal</h4>
        <p>Manage, generate, and renew license keys for the system. This portal allows administrators to:</p>
        <div style="text-align: left; margin-top: 15px;">
            <i class="fas fa-check"></i> Generate new license keys<br>
            <i class="fas fa-check"></i> Extend existing licenses<br>
            <i class="fas fa-check"></i> View license statistics<br>
            <i class="fas fa-check"></i> Export license data
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/2.4.18/js/adminlte.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.3/icheck.min.js"></script>
<script>
    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%'
        });
    });
</script>
</body>
</html>