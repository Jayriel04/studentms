<!-- Enhanced Modern Footer -->
<footer class="modern-footer" role="contentinfo">
  <div class="modern-footer-container">
    <div class="modern-footer-section modern-footer-branding">
      <span class="modern-logo-footer">
        <span class="modern-logo-accent">Student</span>Vue
      </span>
      <div class="modern-footer-system">Student Profiling System</div>
      <div class="modern-footer-desc">Empowering education through digital insight.</div>
    </div>
    <div class="modern-footer-section modern-footer-links">
      <div class="modern-footer-links-list">
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="admin/login.php">Admin</a>
        <a href="staff/login.php">Staff</a>
        <a href="user/login.php">Student</a>
      </div>
      <div class="modern-footer-social">
        <a href="#" aria-label="Twitter" class="modern-footer-social-icon">
          <i class="fab fa-twitter"></i>
        </a>
        <a href="https://www.facebook.com/collegemandauecity" aria-label="Facebook" class="modern-footer-social-icon">
          <i class="fab fa-facebook-f"></i>
        </a>
      </div>
    </div>
    <div class="modern-footer-section modern-footer-contact">
      <?php
      $sql = "SELECT * from tblpage where PageType='contactus'";
      $query = $dbh->prepare($sql);
      $query->execute();
      $results = $query->fetchAll(PDO::FETCH_OBJ);
      if ($query->rowCount() > 0) {
        foreach ($results as $row) { ?>
          <div class="modern-footer-contact-block">
            <span class="modern-footer-contact-title">Contact Us</span>
            <span class="modern-footer-contact-detail"><b>Address:</b> <?php echo $row->PageDescription; ?></span>
            <span class="modern-footer-contact-detail"><b>Phone:</b> <?php echo htmlentities($row->MobileNumber); ?></span>
          </div>
        <?php }
      } ?>
    </div>
  </div>
  <div class="modern-footer-copyright">
    &copy; <?php echo date('Y'); ?> Student Profiling System. All rights reserved.
  </div>
</footer>