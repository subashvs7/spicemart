<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <div class="text-center mb-5">
        <p class="text-saffron fw-500 small text-uppercase">Get In Touch</p>
        <h2 class="section-title d-inline-block">Contact Us</h2>
        <p class="text-muted">We'd love to hear from you. Send us a message and we'll respond within 24 hours.</p>
      </div>

      <div class="row g-4">
        <!-- Contact Info -->
        <div class="col-md-4">
          <div class="d-flex flex-column gap-4">
            <div class="p-4 rounded-xl shadow-soft bg-white">
              <div class="fs-2 mb-2">📍</div>
              <h6 class="fw-600">Our Store</h6>
              <p class="text-muted small mb-0">myeoncasuals,<br>123 Fashion Street, T. Nagar,<br>Chennai – 600 017, Tamil Nadu</p>
            </div>
            <div class="p-4 rounded-xl shadow-soft bg-white">
              <div class="fs-2 mb-2">📞</div>
              <h6 class="fw-600">Call Us</h6>
              <p class="text-muted small mb-0">+91 98765 43210<br>Mon–Sat: 9 AM – 6 PM</p>
            </div>
            <div class="p-4 rounded-xl shadow-soft bg-white">
              <div class="fs-2 mb-2">✉️</div>
              <h6 class="fw-600">Email Us</h6>
              <p class="text-muted small mb-0">support@myeoncasuals.com<br>orders@myeoncasuals.com</p>
            </div>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="col-md-8">
          <div class="bg-white rounded-xl shadow-soft p-4">
            <h5 class="mb-4" style="font-family:'Playfair Display',serif">Send a Message</h5>

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
              <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="post" action="<?php echo site_url('contact'); ?>">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label small fw-600">Full Name *</label>
                  <input type="text" class="form-control" name="name" required
                         value="<?php echo htmlspecialchars($this->input->post('name') ?: ''); ?>"
                         placeholder="Your full name">
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-600">Email *</label>
                  <input type="email" class="form-control" name="email" required
                         value="<?php echo htmlspecialchars($this->input->post('email') ?: ''); ?>"
                         placeholder="you@example.com">
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-600">Phone</label>
                  <input type="tel" class="form-control" name="phone"
                         value="<?php echo htmlspecialchars($this->input->post('phone') ?: ''); ?>"
                         placeholder="10-digit mobile">
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-600">Subject</label>
                  <select class="form-select" name="subject">
                    <option value="">Select subject</option>
                    <option value="Order Query">Order Query</option>
                    <option value="Product Enquiry">Product Enquiry</option>
                    <option value="Return / Refund">Return / Refund</option>
                    <option value="Bulk Order">Bulk Order</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label small fw-600">Message *</label>
                  <textarea class="form-control" name="message" rows="5" required
                            placeholder="Write your message here…"><?php echo htmlspecialchars($this->input->post('message') ?: ''); ?></textarea>
                </div>
                <div class="col-12">
                  <button type="submit" class="btn btn-saffron px-4">
                    <i class="bi bi-send me-2"></i>Send Message
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
