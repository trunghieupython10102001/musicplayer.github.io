const $ = document.querySelector.bind(document)
const $$ = document.querySelectorAll.bind(document)
const playlist = $('.playlist')
const dashboard = $('.dashboard')
const dashboardImg = $('.dashboard__img')
const songName = $('.song-name')
const audio = $('.audio')
const play = $('.play')
let time = $('.time')
const timeBar = $('.dashboard__timer')
let timeLength = 0
const nextBtn = $('.next')
const backBtn = $('.back')
const shuffleBtn = $('.shuffle')
const replayBtn = $('.replay')
let songList
let rotateImg
// console.log(songList)

const app = {
	currentIndex: 0,
	shuffleList: [],
	replay: false,
	songs: [
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
			img: "Believer",
			name: "Believer",
			artist: "Imagine Dragon",
			src: "Believer"
		},
		{
			img: "Call me maybe",
			name: "Call me maybe",
			artist: "Carly Rae Jepsen",
			src: "Call me maybe"
		},
		{
			img: "Cheap Thrills",
			name: "Cheap Thrills",
			artist: "Sia",
			src: "Cheap Thrills"
		},
		{
			img: "Counting stars",
			name: "Counting stars",
			artist: "One Republic",
			src: "Counting stars"
		},
		{
			img: "Dance Monkey",
			name: "Dance Monkey",
			artist: "Tones and I",
			src: "Dance Monkey"
		},
		{
			img: "Happier",
			name: "Happier",
			artist: "Ed Sheeran",
			src: "Happier"
		},
		{
			img: "Havana",
			name: "Havana",
			artist: "Camila Cabello",
			src: "Havana"
		},
		{
			img: "Hymn For The Weekend",
			name: "Hymn For The Weekend",
			artist: "Coldplay",
			src: "Hymn For The Weekend"
		},
		{
			img: "Just give me a reason",
			name: "Just give me a reason",
			artist: "Pink! ft. Nate Ruess",
			src: "Just give me a reason"
		},
		{
			img: "Just the way you are",
			name: "Just the way you are",
			artist: "Bruno Mars",
			src: "Just the way you are"
		},
		{
			img: "Lalala",
			name: "Lalala",
			artist: "Naughty Boy ft. Sam Smith",
			src: "Lalala"
		},
		{
			img: "Lemon tree",
			name: "Lemon tree",
			artist: "Fool's Garden",
			src: "Lemon tree"
		},
		{
			img: "Love is gone",
			name: "Love is gone",
			artist: "Slander",
			src: "Love is gone"
		},
		{
			img: "Love Yourself",
			name: "Love Yourself",
			artist: "Justin Bieber",
			src: "Love Yourself"
		},
		{
			img: "Maps",
			name: "Maps",
			artist: "Maroon 5",
			src: "Maps"
		},
		{
			img: "My Love",
			name: "My Love",
			artist: "Westlife",
			src: "My Love"
		},
		{
			img: "New Rules",
			name: "New Rules",
			artist: "Dua Lipa",
			src: "New Rules"
		},
		{
			img: "One more night",
			name: "One more night",
			artist: "Maroon 5",
			src: "One more night"
		},
		{
			img: "Photograph",
			name: "Photograph",
			artist: "Ed Sheeran",
			src: "Photograph"
		},
		{
			img: "Road",
			name: "Road",
			artist: "Katy Perry",
			src: "Road"
		},
		{
			img: "Rolling in the deep",
			name: "Rolling in the deep",
			artist: "Adele",
			src: "Rolling in the deep"
		},
		{
			img: "See you again",
			name: "See you again",
			artist: "Wiz Khalifa ft. Charlie Puth",
			src: "See you again"
		},
		{
			img: "Senorita",
			name: "Senorita",
			artist: "Camila Cabello ft. Shawn Mendes",
			src: "Senorita"
		},
		{
			img: "Set fire to the rain",
			name: "Set fire to the rain",
			artist: "Adele",
			src: "Set fire to the rain"
		},
		{
			img: "She will be loved",
			name: "She will be loved",
			artist: "Maroon 5",
			src: "She will be loved"
		},
		{
			img: "Someone like you",
			name: "Someone like you",
			artist: "Adele",
			src: "Someone like you"
		},
		{
			img: "Something just like this",
			name: "Something just like this",
			artist: "Coldplay ft. Chainsmokers",
			src: "Something just like this"
		},
		{
			img: "Sugar",
			name: "Sugar",
			artist: "Maroon 5",
			src: "Sugar"
		},
		{
			img: "Talking to the moon",
			name: "Talking to the moon",
			artist: "Bruno Mars",
			src: "Talking to the moon"
		},
		{
			img: "That girl",
			name: "That girl",
			artist: "Olly Murs",
			src: "That girl"
		},
		{
			img: "Titanium",
			name: "Titanium",
			artist: "David Guetta ft. Sia",
			src: "Titanium"
		},
		{
			img: "Treat you better",
			name: "Treat you better",
			artist: "Shawn Mendes",
			src: "Treat you better"
		},
		{
			img: "Uptown Funk",
			name: "Uptown Funk",
			artist: "Mark Ronson ft. Bruno Mars",
			src: "Uptown Funk"
		},
		{
			img: "Waitting for love",
			name: "Waitting for love",
			artist: "Avicii",
			src: "Waitting for love"
		},
		{
			img: "We Don't Talk Anymore",
			name: "We Don't Talk Anymore",
			artist: "Charlie Puth ft. Selena Gomez",
			src: "We Don't Talk Anymore"
		},
		{
			img: "What makes you beautiful",
			name: "What makes you beautiful",
			artist: "One Direction",
			src: "What makes you beautiful"
		},
		{
			img: "When I was your man",
			name: "When I was your man",
			artist: "Bruno mars",
			src: "When I was your man"
		},
		{
			img: "La lung",
			name: "L??? l??ng",
			artist: "V??",
			src: "La lung"
		},
		{
			img: "1 phut",
			name: "1 ph??t",
			artist: "Andiez",
			src: "1 phut"
		},
		{
			img: "Ai la nguoi thuong em",
			name: "Ai l?? ng?????i th????ng em",
			artist: "Anh Qu??n AP",
			src: "Ai la nguoi thuong em"
		},
		{
			img: "Ai mang co don di",
			name: "Ai mang c?? ????n ??i",
			artist: "K-ICM ft. APJ",
			src: "Ai mang co don di"
		},
		{
			img: "Bac phan",
			name: "B???c ph???n",
			artist: "Jack ft. K-ICM",
			src: "Bac phan"
		},
		{
			img: "Bai nay chill phet",
			name: "B??i n??y chill ph???t",
			artist: "??en v??u ft. Min",
			src: "Bai nay chill phet"
		},
		{
			img: "Banh mi khong",
			name: "B??nh m?? kh??ng",
			artist: "?????t G ft. Du Uy??n",
			src: "Banh mi khong"
		},
		{
			img: "Bong hoa dep nhat",
			name: "B??ng hoa ?????p nh???t",
			artist: "Anh Qu??n AP",
			src: "Bong hoa dep nhat"
		},
		{
			img: "Buon lam chi em oi",
			name: "Bu???n l??m chi em ??i",
			artist: "Ho??i L??m",
			src: "Buon lam chi em oi"
		},
		{
			img: "Chieu hom ay",
			name: "Chi???u h??m ???y",
			artist: "Jaykii",
			src: "Chieu hom ay"
		},
		{
			img: "Da lo yeu em nhieu",
			name: "???? l??? y??u em nhi???u",
			artist: "Justa Tee",
			src: "Da lo yeu em nhieu"
		},
		{
			img: "Dua nhau di tron",
			name: "????a nhau ??i tr???n",
			artist: "??en V??u ft. Linh C??o",
			src: "Dua nhau di tron"
		},
		{
			img: "Hai trieu nam",
			name: "Hai tri???u n??m",
			artist: "??en V??u ft. Bi??n",
			src: "Hai trieu nam"
		},
		{
			img: "Het thuong can nho",
			name: "H???t th????ng c???n nh???",
			artist: "?????c Ph??c",
			src: "Het thuong can nho"
		},
		{
			img: "Hoa hai duong",
			name: "Hoa h???i ???????ng",
			artist: "Jack",
			src: "Hoa hai duong"
		},
		{
			img: "Hoa no khong mau",
			name: "Hoa n??? kh??ng m??u",
			artist: "Ho??i L??m",
			src: "Hoa no khong mau"
		},
		{
			img: "HongKong1",
			name: "HongKong1",
			artist: "Nguy???n Tr???ng T??i x San Ji x Double X",
			src: "HongKong1"
		},
		{
			img: "Loving sunny",
			name: "Loving sunny",
			artist: "Kimmese ft. ??en V??u",
			src: "Loving sunny"
		},
		{
			img: "Mot buoc yeu van dam dau",
			name: "M???t b?????c y??u v???n d???m ??au",
			artist: "Mr. Siro",
			src: "Mot buoc yeu van dam dau"
		},
		{
			img: "Mot dem say",
			name: "M???t ????m s???y",
			artist: "Th???nh Suy",
			src: "Mot dem say"
		},
		{
			img: "Muon ruou to tinh",
			name: "M?????n r?????u t??? t??nh",
			artist: "Big Daddy ft. Emily",
			src: "Muon ruou to tinh"
		},
		{
			img: "Nam lay tay anh",
			name: "N???m l???y tay anh",
			artist: "Tu???n H??ng",
			src: "Nam lay tay anh"
		},
		{
			img: "Nang tho",
			name: "N??ng th??",
			artist: "????nh D??ng",
			src: "Nang tho"
		},
		{
			img: "Ngam hoa le roi",
			name: "Ng???m hoa l??? r??i",
			artist: "Ch??u Kh???i Phong",
			src: "Ngam hoa le roi"
		},
		{
			img: "Ngay mai em di",
			name: "Ng??y mai em ??i",
			artist: "L?? Hi???u ft. Soobin Ho??ng S??n",
			src: "Ngay mai em di"
		},
		{
			img: "Nguoi la oi",
			name: "Ng?????i l??? ??i",
			artist: "Orange ft. Karik",
			src: "Nguoi la oi"
		},
		{
			img: "Sai nguoi sai thoi diem",
			name: "Sai ng?????i sai th???i ??i???m",
			artist: "Thanh H??ng",
			src: "Sai nguoi sai thoi diem"
		},
		{
			img: "Song gio",
			name: "S??ng gi??",
			artist: "Jack ft. K-ICM",
			src: "Song gio"
		},
		{
			img: "Thang dien",
			name: "Th???ng ??i??n",
			artist: "Justa Tee ft. Ph????ng Ly",
			src: "Thang dien"
		},
		{
			img: "Thay toi yeu co ay",
			name: "Thay t??i y??u c?? ???y",
			artist: "Thanh H??ng",
			src: "Thay toi yeu co ay"
		},
		{
			img: "Thi thoi",
			name: "Th?? th??i",
			artist: "Reddy",
			src: "Thi thoi"
		},
		{
			img: "Tinh dang nhu ly ca phe",
			name: "T??nh ?????ng nh?? ly c?? ph??",
			artist: "n??n. x Ng??",
			src: "Tinh dang nhu ly ca phe"
		},
		{
			img: "Tron tim",
			name: "Tr???n t??m",
			artist: "??en V??u ft. MTV band",
			src: "Tron tim"
		},
		{
			img: "Vo cung",
			name: "V?? c??ng",
			artist: "Phan Duy Anh",
			src: "Vo cung"
		},
	],
	render() {
		let htmls = this.songs.map(song => {
			return `
				<div class="playlist__item">
					<div class="item-img">
						<img src="./assets/img/${song.img}.jpeg" alt="song-img" class="img-song">
					</div>
					<div class="item-content">
						<p class="item-content__name">${song.name}</p>
						<p class="item-content__artist">${song.artist}</p>
					</div>
					<div class="item-more-info">
						<i class="fas fa-ellipsis-h"></i>
					</div>
				</div>
			`
		})
		playlist.style.marginTop = dashboard.offsetHeight + 'px'
		playlist.innerHTML = htmls.join('\n')
	},
	defineProperties() {
		Object.defineProperty(this, 'currentSong', {
			get() {
				return this.songs[this.currentIndex]
			}
		}) 
	},
	loadCurrentSong() {
		songName.textContent = this.currentSong.name
		dashboardImg.innerHTML = `<img src="./assets/img/${this.currentSong.img}.jpeg" alt="song-img" class="img-song">`
		audio.src = `./assets/audio/${this.currentSong.src}.mp3`
	},
	handleEvents() {
		const dashboardImgHeight = dashboardImg.offsetHeight
		// Handle scroll event
		document.onscroll = function() {
			const scrollTop = window.scrollY 
			const newHeight = dashboardImgHeight - scrollTop 
			dashboardImg.style.height = dashboardImg.style.width = newHeight > 0 ? newHeight + 'px' : 0
			dashboardImg.style.opacity = newHeight / 200
		}

		// Handle click event in play button
		play.onclick = () => {
			if (audio.paused) {
				app.playMusic()
			}
			else {
				app.pauseMusic()
			}
		}

		// Handle timeBar click event
		timeBar.onclick = (e) => {
			audio.currentTime = (e.x - timeBar.offsetLeft) / timeBar.clientWidth * audio.duration
		}

		// Rotate dashboard image
		rotateImg = dashboardImg.animate(
			{
				transform: 'rotate(360deg)'
			}, 
			{
				duration: 20000,
				iterations: Infinity
			}
		)
		rotateImg.pause()
		

		// Handle next click event
		nextBtn.onclick = () => {
			this.nextSong()
			app.playMusic()
		}
		// Handle back click event
		backBtn.onclick = () => {
			this.backSong()
			app.playMusic()
		}

		// Handle shuffle click event
		shuffleBtn.onclick = () => {
			if (shuffleBtn.classList.length === 1) {
				this.shuffleSong()
				this.render()
				app.playMusic()
			} else {
				this.songs = this.shuffleList
				this.currentIndex = 0
				this.loadCurrentSong()
				this.render()
				app.playMusic()
			}
			shuffleBtn.classList.toggle('clicked')
			this.clickSong()
		}

		// Handle replay click event
		replayBtn.onclick = () => {
			if (replayBtn.classList.length === 1) {
				app.replay = true
			} else {
				app.replay = false
			}
			replayBtn.classList.toggle('clicked')
		}

		// Handle songs click event
		this.clickSong()
	},
	runTime() {
		const timeRunning = setInterval(() => {
			timeLength = audio.currentTime / audio.duration * 100 + '%'
			time.style.width = timeLength
			if (audio.ended) {
				rotateImg.pause()
			}
			if (!this.replay) {
				app.autoNext()
			} else {
				if (audio.ended) {
					app.playMusic()
				}
			}
			this.displayPlayingSong()
		}, 10)
	},
	nextSong() {
		this.currentIndex++
		if (this.currentIndex >= this.songs.length) {
			this.currentIndex = 0
		}
		this.loadCurrentSong()
	},
	playMusic() {
		audio.play()
		play.innerHTML = `<i class="fas fa-pause-circle"></i>`
		rotateImg.play()
	},
	pauseMusic() {
		audio.pause()
		play.innerHTML = `<i class="fas fa-play-circle"></i>`
		rotateImg.pause()
	},
	backSong() {
		if (this.currentIndex > 0) {
			this.currentIndex--
		}
		this.loadCurrentSong()
	}, 
	shuffleSong() {
		this.shuffleList = JSON.parse(JSON.stringify(this.songs))
		let maxLength = this.songs.length
		for (let i = 0; i < this.songs.length; i++) {
			let randomIdx = Math.floor(Math.random() * maxLength)
			let tempSong = this.songs[i]
			this.songs[i] = this.songs[randomIdx]
			this.songs[randomIdx] = tempSong
		}
		this.currentIndex = 0
		this.loadCurrentSong()
	},
	autoNext() {
		if (audio.ended) {
			this.currentIndex++
			if (this.currentIndex >= this.songs.length) {
				this.currentIndex = 0
			}
			this.loadCurrentSong()
			this.playMusic()
		}
	},
	replaySong() {
		this.replay = true
	},
	clickSong() {
		songList = playlist.children
		// console.log(songList)
		for (let i = 0; i < songList.length; i++) {
			songList[i].onclick = function(e) {
				app.currentIndex = i
				app.loadCurrentSong()
				app.playMusic()
				
				app.displayPlayingSong()
			}
		}	
	},
	displayPlayingSong() {
		for(let i = 0; i < songList.length; i++) {
			songList[i].classList.remove('playing')
		}
		songList[this.currentIndex].classList.add('playing')
	},
	start() {
		this.defineProperties()
		this.render()
		this.loadCurrentSong()
		this.handleEvents()
		this.runTime()
	}
}

app.start()
