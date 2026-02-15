<!-- Login Modal -->
<div class="modal fade" id="login">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: 2px solid #FFD700;">
      <div class="modal-body" style="padding: 0;">
        <div class="container-fluid" style="display: flex; flex-direction: row; min-height: 400px;">

          <!-- Left Dark Green Panel -->
          <div style="
            background: #004d00;
            color: #FFD700;
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            text-align:center;
          ">
            <h2 style="font-size: 30px; margin-bottom: 20px;">Welcome Back!</h2>
            <p style="font-size: 16px; line-height: 1.5;">
              Please login with your <b>Student ID</b> and <b>Password</b> to access your account.
            </p>
          </div>

          <!-- Right White Panel -->
          <div style="
            background: #fff;
            width: 50%;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
          ">
            <!-- Logo -->
            <img src="images/logo.png" alt="Logo" style="width: 80px; height: auto; margin-bottom: 15px;">

            <h2 style="color: #004d00; margin-bottom: 10px; text-align: center;">Student Login</h2>
            <p style="text-align: center; margin-bottom: 25px; color: #555;">
              Enter your Student ID and Password to continue
            </p>

            <form class="form-horizontal" method="POST" action="login.php" style="width: 100%;">
              
              <!-- Student ID -->
              <div class="form-group">
                <input type="text" class="form-control" id="student" name="student"
                  placeholder="Student ID" required
                  style="width: 100%; padding: 14px; border: 2px solid #004d00; border-radius: 25px;
                  outline: none; font-weight: bold; margin-bottom: 15px;">
              </div>

              <!-- Password -->
              <div class="form-group">
                <input type="password" class="form-control" id="password" name="password"
                  placeholder="Password" required
                  style="width: 100%; padding: 14px; border: 2px solid #004d00; border-radius: 25px;
                  outline: none; font-weight: bold; margin-bottom: 20px;">
              </div>

              <!-- Submit -->
              <button type="submit" class="btn" name="login"
                style="background-color: #004d00; border: 2px solid #FFD700; color: #FFD700;
                width: 100%; padding: 14px; border-radius: 25px; cursor: pointer;
                font-weight: bold; font-size: 16px;">
                <i class="fa fa-sign-in"></i> Login
              </button>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
