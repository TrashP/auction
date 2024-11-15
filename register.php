<?php include_once("header.php") ?>


<div class="container">
  <h2 class="my-3">Register new account</h2>

  <form method="POST" action="process_registration.php">
    <div class="form-group row">
      <label for="accountType" class="col-sm-2 col-form-label text-right">Registering as a:</label>
      <div class="col-sm-10">
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="accountType" id="accountBuyer" value="buyer" checked>
          <label class="form-check-label" for="accountBuyer">Buyer</label>
        </div>
      </div>
      <div class="form-group row">
        <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
        <div class="col-sm-10">
          <input type="text" class="form-control" id="email" name="email" placeholder="Email">
          <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
        </div>
        <small id="accountTypeHelp" class="form-text-inline text-muted"><span class="text-danger">*
            Required.</span></small>
      </div>
    </div>

    <!-- First Name -->
    <div class="form-group row">
      <label for="firstName" class="col-sm-2 col-form-label text-right">First Name</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First Name">
        <small id="firstNameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
      </div>
    </div>

    <!-- Last Name -->
    <div class="form-group row">
      <label for="lastName" class="col-sm-2 col-form-label text-right">Last Name</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last Name">
        <small id="lastNameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
      </div>
    </div>

    <!-- Date of Birth -->
    <div class="form-group row">
      <label for="dateOfBirth" class="col-sm-2 col-form-label text-right">Date of Birth</label>
      <div class="col-sm-10">
        <input type="date" class="form-control" id="dateOfBirth" name="dateOfBirth" aria-describedby="dateOfBirthHelp">
        <small id="dateOfBirthHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
      </div>
    </div>

    <!-- Email -->
    <div class="form-group row">
      <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
      <div class="col-sm-10">
        <input type="text" class="form-control" id="email" name="email" placeholder="Email">
        <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
      </div>
    </div>

    <!-- Password -->
    <div class="form-group row">
      <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
      <div class="col-sm-10">
        <input type="password" class="form-control" id="password" name="password" placeholder="Password">
        <small id="passwordHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
      </div>
    </div>

    <!-- Password Confirmation -->
    <div class="form-group row">
      <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Repeat password</label>
      <div class="col-sm-10">
        <input type="password" class="form-control" id="passwordConfirmation" name="passwordConfirmation"
          placeholder="Enter password again">
        <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">*
            Required.</span></small>
      </div>
      <div class="form-group row">
        <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Repeat password</label>
        <div class="col-sm-10">
          <input type="password" class="form-control" id="passwordConfirmation" name="passwordConfirmation"
            placeholder="Enter password again">
          <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">*
              Required.</span></small>
        </div>
      </div>
      <div class="form-group row">
        <button type="submit" class="btn btn-primary form-control">Register</button>
      </div>
  </form>

  <div class="text-center">Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a>

  </div>

  <div class="text-center">Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a>

  </div>

  <?php include_once("footer.php") ?>