let play = document.querySelector(".play")
let audio = document.querySelector('.audio')
let dashboard = document.querySelector('.dashboard')
let isPlay = false
let dashboardTimer = document.querySelector('.dashboard__timer')
let time = document.querySelector('.time')
let timeLength = 0
let dashboardImg = document.querySelector('.dashboard__img')
let imgRotation = 0
let rotateImg
let playlist = document.querySelector('.playlist')
let listSong = document.getElementsByClassName('playlist__item')
let nextSong = document.querySelector('.next')
let currentSong
let currentElement
let currentSongidx = 0
console.log(nextSong)

let srcList = []

playlist.style.marginTop = dashboard.clientHeight + 'px'



let playlistSong = [
	{
		img: "Attention",
		name: "Attention",
		artist: "Charlie Puth",
		src: "Attention"
	},
	{
		img: "Blank space",
		name: "Blank space",
		artist: "Taylor Swift",
		src: "Blank space"
	},
	{
		img: "Dusk till down",
		name: "Dusk till down",
		artist: "Zayn ft. Sia",
		src: "Dusk till down"
	},
	{
		img: "Girls like you",
		name: "Girls like you",
		artist: "Maroon 5",
		src: "Girls like you"
	},
	{
		img: "Hello",
		name: "Hello",
		artist: "Adele",
		src: "Hello"
	},
	{
		img: "Despacito",
		name: "Despactio",
		artist: "Luis Fonsi ft. Daddy Yankee",
		src: "Despacito"
	},
	{
		img: "Shape of you",
		name: "Shape of you",
		artist: "Ed Sheeran",
		src: "Shape of you"
	},
		
]

currentSong = playlistSong[0]

time.style.width = timeLength + 'px'

playlistSong.forEach(item => {
	srcList.push(item.src)
})

console.log(srcList)

play.addEventListener('click', (e) => {
	if (!audio.paused) {
		clearInterval(rotateImg)
		audio.pause()
		isPlay = false
		play.innerHTML = `<i class="fas fa-play-circle"></i>`
	} else {
		audio.play()
		play.innerHTML = `<i class="fas fa-pause-circle"></i>`
		const timeRunning = setInterval(() => {
			timeLength = audio.currentTime / audio.duration
			time.style.width = timeLength * 100 + '%'
		}, 10)
		if (!audio.ended) {
			rotateImg = setInterval(() => {
				imgRotation += 0.1
				dashboardImg.style.transform = `rotate(${imgRotation}deg)` 
				if (audio.ended) {
					clearInterval(rotateImg)
				}
			}, 10)
		}		
	}
})

dashboardTimer.onclick = (e) => {
	audio.currentTime = (e.x - dashboardTimer.offsetLeft) / dashboardTimer.clientWidth * audio.duration
}

function addPlaylist(playlistSong) {
	playlistSong.forEach(playlistItem => {
		let listItem = document.createElement('div')
		listItem.classList.add('playlist__item')
		listItem.setAttribute('value', `${playlistItem.src}`)
		listItem.innerHTML = `
			<div class="item-img">
				<img src="./assets/img/${playlistItem.img}.jpeg" alt="song-img" class="img-song">
			</div>
			<div class="item-content">
				<p class="item-content__name">${playlistItem.name}</p>
				<p class="item-content__artist">${playlistItem.artist}</p>
			</div>
			<div class="item-more-info">
				<i class="fas fa-ellipsis-h"></i>
			</div>
		`
		playlist.appendChild(listItem)
	})
}

addPlaylist(playlistSong)

for (let i = 0; i < listSong.length; i++) {
	listSong[i].onclick = (e) => {
		currentElement = listSong[i]
		currentSong = playlistSong[i]
		audio.src = `./assets/audio/${currentSong.src}.mp3`
		timeLength = 0
		audio.play()
		dashboardImg.innerHTML = `<img src="./assets/img/${listSong[i].getAttribute('value')}.jpeg" alt="song-img" class="img-song">`
		document.querySelector('.song-name').innerHTML = listSong[i].getAttribute('value')
		// play
		play.innerHTML = `<i class="fas fa-pause-circle"></i>`
		const timeRunning = setInterval(() => {
			timeLength = audio.currentTime / audio.duration
			time.style.width = timeLength * 100 + '%'
		}, 10)
		clearInterval(rotateImg)
		imgRotation = 0
		if (!audio.ended) {
			rotateImg = setInterval(() => {
				imgRotation += 0.1
				dashboardImg.style.transform = `rotate(${imgRotation}deg)` 
				if (audio.ended) {
					clearInterval(rotateImg)
				}
			}, 10)
		}		
	}
}



document.onscroll = (e) => {
	// dashboardImg.style.transform = `scale(${playlist.scrollTop / dashboardImg.clientHeight}%, ${playlist.scrollTop / dashboardImg.clientHeight}%)`
}

nextSong.onclick = e => {
	console.log(currentSong)
	// console.log(currentElement.)
}