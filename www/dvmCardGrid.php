<style>


    HTML CSS 

    Result
    Skip Results Iframe

EDIT ON

:root {
  --red: #ef233c;
  --darkred: #c00424;
  --platinum: #e5e5e5;
  --black: #2b2d42;
  --white: #fff;
  --thumb: #edf2f4;
}

* {
  box-sizing: border-box;
  padding: 0;
  margin: 0;
}

body {
  font: 16px / 24px "Rubik", sans-serif;
  color: var(--black);
  background: var(--platinum);
  margin: 50px 0;
}

.container {
  max-width: 1400px;
  padding: 0 15px;
  margin: 0 auto;
}

h2 {
  font-size: 32px;
  margin-bottom: 1em;
}

.cards {
  display: flex;
  padding: 25px 0px;
  list-style: none;
  overflow-x: scroll;
  scroll-snap-type: x mandatory;
}

.card {
  display: flex;
  flex-direction: column;
  flex: 0 0 100%;
  padding: 20px;
  background: var(--white);
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 15%);
  scroll-snap-align: start;
  transition: all 0.2s;
}

.card:not(:last-child) {
  margin-right: 10px;
}

.card:hover {
  color: var(--white);
  background: var(--red);
}

.card .card-title {
  font-size: 20px;
}

.card .card-content {
  margin: 20px 0;
  max-width: 85%;
}

.card .card-link-wrapper {
  margin-top: auto;
}

.card .card-link {
  display: inline-block;
  text-decoration: none;
  color: white;
  background: var(--red);
  padding: 6px 12px;
  border-radius: 8px;
  transition: background 0.2s;
}

.card:hover .card-link {
  background: var(--darkred);
}

.cards::-webkit-scrollbar {
  height: 12px;
}

.cards::-webkit-scrollbar-thumb,
.cards::-webkit-scrollbar-track {
  border-radius: 92px;
}

.cards::-webkit-scrollbar-thumb {
  background: var(--darkred);
}

.cards::-webkit-scrollbar-track {
  background: var(--thumb);
}

@media (min-width: 500px) {
  .card {
    flex-basis: calc(50% - 10px);
  }

  .card:not(:last-child) {
    margin-right: 20px;
  }
}

@media (min-width: 700px) {
  .card {
    flex-basis: calc(calc(100% / 3) - 20px);
  }

  .card:not(:last-child) {
    margin-right: 30px;
  }
}

@media (min-width: 1100px) {
  .card {
    flex-basis: calc(25% - 30px);
  }

  .card:not(:last-child) {
    margin-right: 40px;
  }
}


/* FOOTER STYLES
–––––––––––––––––––––––––––––––––––––––––––––––––– */
.page-footer {
  position: fixed;
  right: 0;
  bottom: 50px;
  display: flex;
  align-items: center;
  padding: 5px;
  z-index: 1;
}

.page-footer a {
  display: flex;
  margin-left: 4px;
}


</style>
<html>
<div class="container">
  <h2>Scrolling Card UI With CSS Grid</h2>
  <ul class="cards">
    <li class="card">
      <div>
        <h3 class="card-title">Service 1</h3>
        <div class="card-content">
          <p>Lorem ipsum dolor sit amet consectetur adipisicing elit.</p>
        </div>
      </div>
      <div class="card-link-wrapper">
        <a href="" class="card-link">Learn More</a>
      </div>
    </li>
    <li class="card">
      <div>
        <h3 class="card-title">Service 2</h3>
        <div class="card-content">
          <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ab repudiandae magnam harum natus fuga et repellat in maiores.</p>
        </div>
      </div>
      <div class="card-link-wrapper">
        <a href="" class="card-link">Learn More</a>
      </div>
    </li>
    <li class="card">
      <div>
        <h3 class="card-title">Service 3</h3>
        <div class="card-content">
          <p>Phasellus ultrices lorem vel bibendum ultricies. In hendrerit nulla a ante dapibus pulvinar eu eget quam.</p>
        </div>
      </div>
      <div class="card-link-wrapper">
        <a href="" class="card-link">Learn More</a>
      </div>
    </li>
    <li class="card">
      <div>
        <h3 class="card-title">Service 4</h3>
        <div class="card-content">
          <p>Aenean posuere mauris quam, pellentesque auctor mi bibendum nec. Sed scelerisque lacus nisi, quis auctor lorem ornare vel.</p>
        </div>
      </div>
      <div class="card-link-wrapper">
        <a href="" class="card-link">Learn More</a>
      </div>
    </li>
    <li class="card">
      <div>
        <h3 class="card-title">Service 5</h3>
        <div class="card-content">
          <p>Vestibulum pharetra fringilla felis sit amet tempor. Interdum et malesuada fames ac ante ipsum primis in faucibus. Cras et arcu sit amet est consequat feugiat. Nam ut sapien pulvinar.</p>
        </div>
      </div>
      <div class="card-link-wrapper">
        <a href="" class="card-link">Learn More</a>
      </div>
    </li>
    <li class="card">
      <div>
        <h3 class="card-title">Service 6</h3>
        <div class="card-content">
          <p>Donec ut tincidunt nisl. Vivamus eget eros id elit feugiat mollis. Nam sed sem quis libero finibus tempor.</p>
        </div>
      </div>
      <div class="card-link-wrapper">
        <a href="" class="card-link">Learn More</a>
      </div>
    </li>
    <li class="card">
      <div>
        <h3 class="card-title">Service 7</h3>
        <div class="card-content">
          <p>Aliquam eget nisl auctor, sollicitudin ipsum at, dignissim ligula. Donec tincidunt in elit et pellentesque. Integer posuere metus ac massa mollis euismod.</p>
        </div>
      </div>
      <div class="card-link-wrapper">
        <a href="" class="card-link">Learn More</a>
      </div>
    </li>
    <li class="card">
      <div>
        <h3 class="card-title">Service 8</h3>
        <div class="card-content">
          <p> Vivamus eget eros id elit feugiat mollis. Nam sed sem quis libero finibus tempor.</p>
        </div>
      </div>
      <div class="card-link-wrapper">
        <a href="" class="card-link">Learn More</a>
      </div>
    </li>
    <li class="card">
      <div>
        <h3 class="card-title">Service 9</h3>
        <div class="card-content">
          <p>Duis id congue turpis. Donec sodales porta felis, nec ultricies ante. Nam placerat vitae metus sit amet tempor. Aliquam ac dictum est.</p>
        </div>
      </div>
      <div class="card-link-wrapper">
        <a href="" class="card-link">Learn More</a>
      </div>
    </li>
    <li class="card">
      <div>
        <h3 class="card-title">Service 10</h3>
        <div class="card-content">
          <p>Pellentesque eget eros eget justo efficitur fermentum.</p>
        </div>
      </div>
      <div class="card-link-wrapper">
        <a href="" class="card-link">Learn More</a>
      </div>
    </li>
    <li class="card">
      <div>
        <h3 class="card-title">Service 11</h3>
        <div class="card-content">
          <p>Phasellus posuere nec nibh ut tincidunt. Aenean mollis turpis non eros posuere, at luctus leo hendrerit. Integer non libero sapien.</p>
        </div>
      </div>
      <div class="card-link-wrapper">
        <a href="" class="card-link">Learn More</a>
      </div>
    </li>
    <li class="card">
      <div>
        <h3 class="card-title">Service 12</h3>
        <div class="card-content">
          <p>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Vestibulum ornare metus ac lectus scelerisque volutpat.</p>
        </div>
      </div>
      <div class="card-link-wrapper">
        <a href="" class="card-link">Learn More</a>
      </div>
    </li>
  </ul>
</div>
<footer class="page-footer">
  <span>made by </span>
  <a href="https://georgemartsoukos.com/" target="_blank">
    <img width="24" height="24" src="https://assets.codepen.io/162656/george-martsoukos-small-logo.svg" alt="George Martsoukos logo">
  </a>
          </html>