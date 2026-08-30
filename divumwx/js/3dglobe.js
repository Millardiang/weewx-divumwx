const renderer = new THREE.WebGLRenderer({
  alpha: true,
  premultipliedAlpha: false
});
renderer.setSize( Width = 300, Height = 150 );
document.body.appendChild( renderer.domElement );

const scene = new THREE.Scene();

const color = 0xFFFFFF;
var light = new THREE.DirectionalLight(0xffffff, 1);
light.position.set(5,3,5);
scene.add(light);

const camera = new THREE.PerspectiveCamera( 75, Width / Height, 0.1, 1000 );

// const controls = new THREE.OrbitControls( camera, renderer.domElement );

const textureLoader = new THREE.TextureLoader();
textureLoader.crossOrigin = "Anonymous";
const texture = textureLoader.load('https://i.postimg.cc/pLmmJ2Gp/2-no-clouds-4k.jpg');

const geometry = new THREE.SphereGeometry(0.75, 32, 32);
const material = new THREE.MeshPhongMaterial( { 
  color: new THREE.Color(0x3B43D2), 
  // opacity: 0.5, 
  // transparent: true,
  map: texture
} );

const sphere = new THREE.Mesh( geometry, material );

scene.add( sphere );
scene.add(new THREE.AmbientLight(0x333333));

camera.position.z = 5; // 5

//const controls = new THREE.OrbitControls(camera, renderer.domElement);

const animate = function () {
  requestAnimationFrame( animate );
  
//  controls.update();
  sphere.rotation.y += 0.0025;
  // controls.update();
  // clouds.rotation.y += 0.0005;  

  renderer.render( scene, camera );
};

animate();
