

<style>

//variable
$hover: #EC1C24


//body
body
  font-family: 'Roboto', sans-serif
  background: #fdfdfd
  position: relative
  margin: 0

//hamburger section
.humburger 
  width: 33px
  height: 28px
  display: flex
  flex-direction: column
  align-items: center
  justify-content: space-between
  position: absolute
  right: 40px
  top: 30px
  cursor: pointer
  transition: 0.5s all ease-in
  z-index: 5555


  //humberger after background with transition
  &:after 
    content: ""
    position: absolute
    top: 0
    left: 0
    bottom: 0
    right: 0
    background: $hover
    transform: scale(0)
    transition: 0.5s all ease-in
    z-index: 1
  


  //humber line
  .line 
    height: 4px
    width: 100%
    background: #1f6b0c
    display: flex
    transition: 0.5s all ease-in
    position: relative
    z-index: 2


    //line after background
    &:after 
      content: ""
      position: absolute
      height: 100%
      left: 0
      top: 0
      bottom: 0
      background: white
      width: 0
      transition: 0.5s all ease-in-out
      z-index: 1
    
  


  //hamburger section hover
  &:hover 

    &:after 
      transform: scale(1.4)
    

    .line 
      &:after
        width: 100%
      


  &.open 

    .line 
      background: white

      &:nth-of-type(1) 
        transform: rotate(45deg)
        margin-top: 12px
      

      &:nth-of-type(2) 

        transform: rotate(-45deg)
        margin-top: -13px
      
      &:nth-of-type(3) 
        visibility: hidden
        opacity: 0


//main menu body
.menu_body 
  position: fixed
  top: 0
  right: 0
  bottom: 0
  width: 350px
  height: 100vh
  display: flex
  background: rgba(0, 0, 0, 0.8)
  transform: translateX(100%)
  transition: 0.5s all ease

  &.open 
    transform: translateX(0)


  &__item_wrapper 
    padding: 40px 40px 40px 0
    display: flex
    justify-content: flex-start
    align-items: flex-end
    width: 100%


    //menu list
    .menu_list 
      list-style: none
      width: 100%
      padding: 0 0 0 0


      li 
        margin-bottom: 30px
        position: relative
        padding-left: 55px

        &:last-child 
          margin-bottom: 0
        


        a 
          color: white
          font-weight: 400
          font-size: 18px
          text-decoration: none
          position: relative
          transition: 0.5s all ease

          &:after 
            content: ""
            position: absolute
            height: 1px
            left: 0
            width: 0
            bottom: 0
            background: $hover
            transition: 0.5s all ease
          

          &:hover 
            &:after 
              width: 100%
            
          
        


        //have submenu
        &.has_child 
          .sub-menu 
            position: absolute
            background: black
            list-style: none
            padding: 30px
            left: unset
            top: 0
            right: 100%
            min-width: 135px
            opacity: 0
            visibility: hidden

            li 
              padding-left: 0
              margin-bottom: 15px

              a 
                &:after 
                  display: none
     

          &:hover 
            .sub-menu 
              opacity: 1
              visibility: visible
 


//mobile responsive

@media(max-width: 992px) 
  body 
    .menu_body 
      width: 100%


      &__item_wrapper 
        .menu_list 
          li.has_child 
            position: relative

            a 
              position: relative
              &:after 
                content: "+"
                color: white
                left: calc(100% + 25px)
                position: absolute
                top: 0
                width: 0
   
            .sub-menu 
              position: relative
              background: transparent
              list-style: none
              padding: 0
              left: unset
              top: unset
              right: unset
              min-width: auto
              opacity: 1
              visibility: visible
              margin-top: 15px
              padding-left: 25px
              display: none
</style>
              <html>
<!--  hamburger section-->
<div class="humburger" id="hambuger_menu">
  <div class="line"></div>
  <div class="line"></div>
  <div class="line"></div>
</div>


<!--main menu body-->
<section class="menu_body" id="menubody">
  <div class="menu_body__item_wrapper">
<!--    menu list-->
    <ul class="menu_list">
      <li><a href="#">Home</a></li>
<!--      have submenu-->
      <li class="has_child"><a href="#">About</a>
        <ul class="sub-menu">
          <li><a href="#">Who We Are</a></li>
          <li><a href="#">Managment</a></li>
        </ul>
      </li>
<!--      have submenu-->
      <li class="has_child"><a href="javascript">Projects</a>
        <ul class="sub-menu">
          <li><a href="#">Ongoing</a></li>
          <li><a href="#">Complete</a></li>
        </ul>
      </li>
      <li><a href="#">Brochure</a></li>
      <li><a href="#">FAQ</a></li>
      <li><a href="#">Contact Us</a></li>

    </ul>


  </div>
</section>

<script>
 $(document).ready(function () {
    var display_width = $(window).width();
    var hamburger = $("#hambuger_menu");
    var menu = $("#menubody");

    $(hamburger).click(function (e) {
      menu.toggleClass("open");
      hamburger.toggleClass("open");
    });

    $(".menu_body__item_wrapper li.has_child").each(function (index) {
      $(this).click(function (event) {
        $('.sub-menu').eq(index).slideToggle();
        event.preventDefault();
        event.stopImmediatePropagation();

      });
      $('.sub-menu').click(function (e){
        e.stopPropagation();
        e.stopImmediatePropagation();
      })
    })


  })
</script>
</html>