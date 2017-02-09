export default {

	PanelBlock:
`<div class="card card-overlay">
		<div class="card-body">
			<div class="card-title-wrap  pull-right  ">
				<h2 class="card-title">Block heading</h2>
				<p class="card-text">Block description text</p>
			</div>
			<div class="card-media-wrap " data-mode="fullscreen">
				<img class="card-img" src="https://beta.kent.ac.uk/examples/images/hummingbird.jpg" alt="a hummingbird" title="">
			</div>

			<div class=" card-img-overlay-bottom-shaded  card-overlay-inline-sm">
				<h3 class="card-subtitle" id="overlay-subtitle">Overlay subtitle</h3>
				<p class="card-text">Overlay description</p>
			</div>
		</div>

		<div class="card-footer">
			<a href="https://beta.kent.ac.uk/examples/patterns/list.html#" class="chevron-link active" aria-expanded="true">Related link 1</a>
			<a href="https://beta.kent.ac.uk/examples/patterns/list.html#" class="chevron-link active" aria-expanded="true">Related link 2</a>
			<a href="https://beta.kent.ac.uk/examples/patterns/list.html#" class="chevron-link active" aria-expanded="true">Related link 3</a>
			<a href="https://beta.kent.ac.uk/examples/patterns/list.html#" class="chevron-link active" aria-expanded="true">Related link 4</a>
		</div>
	</div>`,

	PanelBlock2:
`<div class="card card-overlay editorial editorial-">
	<div class="card-body">
		<div class="card-title-wrap pull-right">
			<h2 class="card-title">An editorial panel</h2>
			<p>Kent is not only a great place to study, it’s also a great place to live.  Every day you will encounter new cultures, interests and backgrounds. </p>

			<h3><a href="https://beta.kent.ac.uk/courses/undergraduate/student-experience/index.html" class="chevron-link">Student experience</a></h3>
				<p>Coming to Kent  will be life-changing.  Enjoy new experiences from the moment you arrive until you graduate.</p>

			<h3><a href="https://www.kent.ac.uk/locations/" class="chevron-link">Stunning locations</a></h3>
				<p>We have two stunning campuses and a part-time study centre in Kent, plus study centres at four major cities across Europe.</p>
			<h3><a href="https://beta.kent.ac.uk/courses/undergraduate/accommodation/index.html" class="chevron-link">Modern accommodation</a></h3>
			<p>Where you live  is a big decision. See our range of accommodation  and prices to make sure you get it right.</p>
		</div>
		<div class="card-media-wrap">
			<img class="card-img-top" alt="Violinist" src="https://beta.kent.ac.uk/examples/images/unsplash-violinist-3x2.jpg">
		</div>
	</div>
	<div class="card-footer">
		<a href="https://beta.kent.ac.uk/examples/undergraduate/beta.knt" class="chevron-link active" aria-expanded="true">Test link</a>
		<a href="https://beta.kent.ac.uk/examples/undergraduate/beta.knt" class="chevron-link active" aria-expanded="true">Test link</a>
		<a href="https://beta.kent.ac.uk/examples/undergraduate/beta.knt" class="chevron-link active" aria-expanded="true">Test link</a>
		<a href="https://beta.kent.ac.uk/examples/undergraduate/beta.knt" class="chevron-link active" aria-expanded="true">Test link</a>
	</div>
</div>`,

	QUoteBlock:
`<blockquote class="social-quote social-quote-twitter ">
	<a href="https://beta.kent.ac.uk/examples/undergraduate/test">
		<button></button>
		<p>This is a quote from someone</p>
	</a>
	<cite>
		<a href="http://kent.ac.uk/">@unikent</a>
	</cite>
</blockquote>`,

	TextBlock:
`<div class="content-container pb-5 mt-3">
	<div class="content-main">
		<h2>Feature panels</h2>
		<p>These are used to make a strong marketing statement hightlighting key features on a landing page and allowing users to navigate to further details.</p>
		<p>Other instances of the feature panels on this page show how they can also incorporate other media to support your content to add interest and interaction:
		</p>

		<ul>
			<li><a href="https://beta.kent.ac.uk/examples/patterns/list.html#video">video</a></li>
			<li><a href="https://beta.kent.ac.uk/examples/patterns/list.html#loop">loops</a></li>
			<li><a href="https://beta.kent.ac.uk/examples/patterns/list.html#map">maps</a></li>
			<li><a href="https://beta.kent.ac.uk/examples/patterns/list.html#sliders">sliders</a></li>
			<li><a href="https://beta.kent.ac.uk/examples/patterns/list.html#cta">call-to-action</a></li>
			<li> <a href="https://beta.kent.ac.uk/examples/patterns/list.html#search">search</a></li>
		</ul>

		<p>They rely strongly on quality images and videos and should not be used unless quality imagery and video can be supported.</p>
		<p>If you need to elaborate on a specific topic, an
		<a href="https://beta.kent.ac.uk/examples/patterns/list.html#editorial"> editorial feature panel</a> may be better.</p>
	</div>
</div>`,

	TextBlock2:
`<div class="content-container pb-5">
	<div class="content-main">
		<h2>Editorial feature panel</h2>
		<ul>
			<li>Short overview with slightly more detail that a standard feature panel.</li>
			<li>The content can contain a maximum of 100 words - about 670 characters. Less is the heading goes onto two lines. </li>
		</ul>
	</div>
</div>`,

	TextBlock3:
`<div class="content-container pb-3 mt-3">
	<div class="content-main">

		<h3>Block heading</h3>
		<ul>
			<li>Displays the topic heading</li>
			<li>Use a short heading, try stick to two lines on desktop - about 32 characters.</li>
			<li>Can be positioned right or left - positiion with consideration to the subject of the panel image.</li>
		</ul>

		<h3>Block description text</h3>
		<ul>
			<li>Keep short and concise, about 4 lines on desktop - 210 characters.</li>
		</ul>

		<h3 id="panel-image">Panel image/video</h3>
		<ul>
			<li>A strong, impactful image (or video) makes a panel feature successful. [image guidelines link]</li>
			<li>Only use quality images. We recommend a ratio of 3:2 with a minimum size of 1920x1080px.</li>
			<li>The placement of the subject of the image will need consideration to work with the block heading.</li>
		</ul>

		<h3>Overlay subtitle and description</h3>
		<ul>
			<li>Can be optionally used to highlight a specific instance of the topic. For example, if the feature is about "Research at Kent", the subtitle could display a specific research project and link to it.</li>
			<li>Keep the description text short - less than 90 characters</li>
		</ul>

		<h3>Related links</h3>
		<ul>
			<li>You can have up to 4 related links.</li>
			<li>Keep the text short - max 30 characters.</li>
		</ul>

	</div>
</div>`,

};
