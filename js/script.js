'use strict';
var openedMenu = false;
var openedDataDesc = false;
var openDataDescBtn = document.querySelector('.products__data-open-btn');
var openMenuBtn = document.querySelector('.show-nav');
var menu = document.querySelector('.main-menu');
// var messengers = document.querySelector('.messengers');

if (screen.width < 780) {
  document.querySelector('.desc').classList.add('visually-hidden');
} else {
  document.querySelector('.desc').classList.remove('visually-hidden');
}

openMenuBtn.addEventListener('click', function () {
  if (!openedMenu) {
    menu.style.display = 'block';
    // messengers.style.display = 'flex';
    openedMenu = true;
  } else {
    menu.style.display = 'none';
    // messengers.style.display = 'none';
    openedMenu = false;
  }
});

// openDataDescBtn.addEventListener('click', function () {
//   openDataDescBtn.nextElementSibling.style.display = 'block';
// });
$('.products__open-btn').on('click', function () {
  $(this).next().show();
  $(this).hide();
});
// var DESCTOP_WIDTH = 1024;
// var DESCTOP_PADDING = 30;
// var MOBILE_PADDING = 20;
// var visibleBlock = document.querySelector('.visible-block');
// var leftLabel = document.querySelector('label[for="show-left"]');
// var rightLabel = document.querySelector('label[for="show-right"]');
// var leftInput = document.querySelector('.show-input--left');
// var rightInput = document.querySelector('.show-input--right');
// var startFixY = visibleBlock.getBoundingClientRect().top;
// var startRight = visibleBlock.getBoundingClientRect().right;
//
// var getScrollY = function(){
//  if(window.pageYOffset!= undefined){
//   return pageYOffset;
//  } else{
//   var d= document, r= d.documentElement, b= d.body;
//   return r.scrollTop || b.scrollTop || 0;
//  }
// };
//
// window.addEventListener("scroll", function(event) {
//     var top = getScrollY() + 40;
//         if (top >= startFixY) {
//           visibleBlock.style.position = "fixed";
//           visibleBlock.style.top = "40px";
//           if (screen.width >= DESCTOP_WIDTH) {
//             visibleBlock.style.right = startRight - DESCTOP_WIDTH + DESCTOP_PADDING * 2 + 'px';
//           } else {
//             visibleBlock.style.right = startRight - screen.width + MOBILE_PADDING * 2 + 'px';
//           }
//         } else {
//           visibleBlock.style.position = "relative";
//           visibleBlock.style.top = "";
//           visibleBlock.style.right = "";
//         }
// }, false);
//
// leftInput.addEventListener('change', function() {
//   leftInput.checked ? leftLabel.textContent = 'Hide left panel' : leftLabel.textContent = 'Show left panel';
// });
//
// rightInput.addEventListener('change', function() {
//   rightInput.checked ? rightLabel.textContent = 'Hide left panel' : rightLabel.textContent = 'Show left panel';
// });
//
// leftLabel.addEventListener('keydown', function(evt) {
//   if (evt.keyCode == 13) {
//     leftInput.checked ? leftInput.checked = false : leftInput.checked = true;
//   }
// });
//
// rightLabel.addEventListener('keydown', function(evt) {
//   if (evt.keyCode == 13) {
//     rightInput.checked ? rightInput.checked = false : rightInput.checked = true;
//   }
// });
