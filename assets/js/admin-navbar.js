$(document).ready(function() {
    // Toggle the side navigation
    $('#sidebarToggle').on('click', function(e) {
      e.preventDefault();
      $('body').toggleClass('sidebar-toggled');
      $('.sidebar').toggleClass('toggled');
      
      if ($('.sidebar').hasClass('toggled')) {
        $('.sidebar .collapse').collapse('hide');
      }
    });
  
    // Close any open menu accordions when window is resized below 768px
    $(window).resize(function() {
      if ($(window).width() < 768) {
        $('.sidebar .collapse').collapse('hide');
      }
    });
  
    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
      if ($(window).width() > 768) {
        var e0 = e.originalEvent,
            delta = e0.wheelDelta || -e0.detail;
        this.scrollTop += (delta < 0 ? 1 : -1) * 30;
        e.preventDefault();
      }
    });
  
    // Scroll to top button appear
    $(document).on('scroll', function() {
      var scrollDistance = $(this).scrollTop();
      if (scrollDistance > 100) {
        $('.scroll-to-top').fadeIn();
      } else {
        $('.scroll-to-top').fadeOut();
      }
    });
  
    // Smooth scrolling using jQuery easing
    $(document).on('click', 'a.scroll-to-top', function(e) {
      var $anchor = $(this);
      $('html, body').stop().animate({
        scrollTop: ($($anchor.attr('href')).offset().top)
      }, 1000, 'easeInOutExpo');
      e.preventDefault();
    });
  });
  // ตรวจสอบให้แน่ใจว่า DOM โหลดเสร็จแล้ว
document.addEventListener('DOMContentLoaded', function() {
  // เปิด/ปิด Sidebar
  $('#sidebarToggle').on('click', function() {
    $('body').toggleClass('sidebar-toggled');
    $('.sidebar').toggleClass('toggled');
  });

  // เปิด/ปิด Dropdown เมื่อคลิก
  $('.dropdown-toggle').on('click', function(e) {
    e.preventDefault();
    
    // ปิด dropdown อื่นๆ ที่เปิดอยู่
    $('.dropdown-menu').not($(this).next('.dropdown-menu')).removeClass('show');
    
    // เปิด/ปิด dropdown ปัจจุบัน
    $(this).next('.dropdown-menu').toggleClass('show');
  });

  // ปิด dropdown เมื่อคลิกที่อื่นในหน้า
  $(document).on('click', function(e) {
    if (!$(e.target).closest('.dropdown').length) {
      $('.dropdown-menu').removeClass('show');
    }
  });
});
