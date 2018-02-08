<template>
	<div v-loading.fullscreen.lock="loading">

	<link rel="stylesheet" href="//www-test.kent.ac.uk/patterns/kent-theme-assets/assets/css/main.min.css" />
	<link rel="stylesheet" href="//www-test.kent.ac.uk/patterns/kent-theme-assets/assets/css/kentfont.css" />
	
	<section class="panel panel--content panel--grid panel--spaced panel--tertiary">
		<div class="panel__body panel__body--profile">
			<div class="profile profile--media">
				<!-- TODO image with fallback-->

				<img v-if="img" class="profile__image" :src="img.url" :alt="`Portrait of ${name}`">
				<img v-else class="profile__image" src="http://pantheon.app.www-dev.kent.ac.uk/websolutions/kentontheweb/images/profile.jpg">
			
				
				<h1 class="profile__title">{{ name }}</h1>
				<div class="profile__text">
					<!-- TODO this should be an array of strings -->
					{{ profileData.roles }}
					<!-- <div v-for="item in profileData.roles">{{ item }}</div> -->
				</div>
				<div class="profile__social">
					<a v-for="item in profileData.socialmedia"
						class="button button--social button--white-social"
						:class="[`button--${item.name.toLowerCase()}`,`button--${item.name.toLowerCase()}-color-hover`,`kf-${item.name.toLowerCase()}`]"
						:href="item.link"
					></a>
				</div>
			</div>
		</div>
	</section>

	<section class="panel panel--content panel--grid-content panel--tight">
		<div class="panel__body">

			<nav v-if="profileData.email || profileData.telephone" class="content content--aside content--aside-top">
				<ul class="info-sidebar">
					<li>
						<i class="info-sidebar__icon kf-user"></i><h3 class="info-sidebar__title">Contact</h3>
						<ul class="info-sidebar__section">
							<li class="info-sidebar__item"><span v-if="profileData.email">{{ profileData.email }}</span><br><span v-if="profileData.telephone">{{ profileData.telephone }}</span></li>
						</ul>
					</li>
				</ul>
			</nav>

			<div class="content content--main content--profile">

				<nav class="jump-links jump-links--white">
					<h2 class="jump-links__title">On this page</h2>
					<ul class="jump-links__list">
						<li v-if="profileData.about" class="jump-links__item"><a class="jump-links__link" href="#about">About</a></li>
						<li v-if="profileData.research_interests || profileData.research_interest_highlight" class="jump-links__item"><a class="jump-links__link" href="#research">Research interests</a></li>
						<li v-if="profileData.teaching" class="jump-links__item"><a class="jump-links__link" href="#teaching">Teaching</a></li>
						<li v-if="profileData.supervision" class="jump-links__item"><a class="jump-links__link" href="#supervision">Supervision</a></li>
						<li v-if="profileData.professional" class="jump-links__item"><a class="jump-links__link" href="#professional">Professional</a></li>
						<li v-if="publications" class="jump-links__item"><a class="jump-links__link" href="#publications">Publications</a></li>
					</ul>
				</nav>

				<section v-if="profileData.about" id="about">
					<h2>About</h2>
					<pre>{{ profileData.about }}</pre>
				</section>

				<section v-if="profileData.research_interests || profileData.research_interest_highlight" id="research">
					<h2>Research interests</h2>
					<p><pre v-if="profileData.research_interest_highlight">{{ profileData.research_interest_highlight }}</pre></p>
					<pre v-if="profileData.research_interests">{{ profileData.research_interests }}</pre>
				</section>

				<section v-if="profileData.teaching" id="teaching">
					<h2>Teaching</h2>
					<pre>{{ profileData.teaching }}</pre>
				</section>

				<section v-if="profileData.supervision" id="supervision">
					<h2>Supervision</h2>
					<pre>{{ profileData.supervision }}</pre>
				</section>

				<section v-if="profileData.professional" id="professional">
					<h2>Professional</h2>
					<pre>{{ profileData.professional }}</pre>
				</section>

				<!-- TODO how do we know if they have publications?? -->
				<section v-if="publications" id="publications">
					<h2>Publications</h2>
					<div class="KARWidget">
						<p>Also view these in the <a class="KARExternal" :href="karLink" title="View these publications in the Kent Academic Repository" target="_blank">Kent Academic Repository</a></p>
					</div>
					<div class="KARHeader">
					</div>
					<!-- TODO publications and publication data will come from the API  -->
					<div class="KARBody">
						<h3 class="KARBlockHeader">Article</h3>
						<ul class="KARRows">
							<li class="KARItem">
								<div class="KARPub">
									<!--<a title="view publication details in KAR" target="_self" href="http://dx.doi.org/10.1249/MSS.0000000000001441">Caffeine Ingestion Attenuates Fatigue-induced Loss of Muscle Torque Complexity</a> -->
								<div class="csl-bib-body"><div style="text-indent: -25px; padding-left: 25px;"><div class="csl-entry">Pethick, J., Winter, S. and Burnley, M. (2017).<span class="citeproc-title"> Caffeine Ingestion Attenuates Fatigue-induced Loss of Muscle Torque Complexity</span>. <span class="citeproc-container-title"><span style="font-style: italic;">Medicine &amp; Science in Sports &amp; Exercise</span></span> <span class="citeproc-online">[Online]</span>. <span class="citeproc-available at">Available at</span>: <span class="citeproc-URL">http://dx.doi.org/10.1249/MSS.0000000000001441</span>.</div></div></div>    </div>
								<div class="KARLinks" style="padding-left: 25px;">
									<p><a title="click to view/hide the abstract" class="KARToggleAbstract" tabindex="0">Abstract</a> | <a href="https://kar.kent.ac.uk/63920" title="View in KAR">View in KAR</a>        </p>
									<div class="KARAbstract panel panel-secondary" style="display: none;">Purpose
									We tested the hypothesis that caffeine administration would attenuate the fatigue-induced loss of torque complexity.

									Methods
									Eleven healthy participants performed intermittent isometric contractions of the knee extensors to task failure at a target torque of 50% maximal voluntary contraction (MVC), with a 60% duty factor (6 s contraction, 4 s rest), 60 min after ingesting 6 mg·kg−1 caffeine or a placebo. Torque and surface EMG signals were sampled continuously. Complexity and fractal scaling of torque were quantified using approximate entropy (ApEn) and the detrended fluctuation analysis (DFA) α scaling exponent. Global, central and peripheral fatigue were quantified using MVCs with femoral nerve stimulation.

									Results
									Caffeine ingestion increased endurance by 30 ± 16% (mean ± SD, P = 0.019). Complexity decreased in both trials (decreased ApEn, increased DFA α; both P &lt; 0.01), as global, central and peripheral fatigue developed (all P &lt; 0.01). Complexity decreased significantly more slowly following caffeine ingestion (ApEn, -0.04 ± 0.02 vs. –0.06 ± 0.01, P = 0.004; DFA α, 0.03 ± 0.02 vs. 0.04 ± 0.03, P = 0.024), as did the rates of global (-18.2 ± 14.1 vs. –23.0 ± 17.4 N.m.min−1, P = 0.004) and central (-3.5 ± 3.4 vs. –5.7 ± 3.9 %·min−1, P = 0.02) but not peripheral (-6.1 ± 4.1 vs. –7.9 ± 6.3 N.m.min−1, P = 0.06) fatigue.

									Conclusion
									Caffeine ingestion slowed the fatigue-induced loss of torque complexity and increased the time to task failure during intermittent isometric contractions, most likely through central mechanisms.</div>    </div>
									</li><li class="KARItem">

								<div class="KARPub">
									<!--<a title="view publication details in KAR" target="_self" href="http://dx.doi.org/10.1080/17461391.2016.1249524">Power-duration relationship: physiology, fatigue and the limits of human performance</a> -->
								<div class="csl-bib-body"><div style="text-indent: -25px; padding-left: 25px;"><div class="csl-entry">Burnley, M. and Jones, A. (2016).<span class="citeproc-title"> Power-duration relationship: physiology, fatigue and the limits of human performance</span>. <span class="citeproc-container-title"><span style="font-style: italic;">European Journal of Sport Science</span></span> <span class="citeproc-online">[Online]</span>. <span class="citeproc-available at">Available at</span>: <span class="citeproc-URL">http://dx.doi.org/10.1080/17461391.2016.1249524</span>.</div></div></div>    </div>
								<div class="KARLinks" style="padding-left: 25px;">
									<p><a title="click to view/hide the abstract" class="KARToggleAbstract" tabindex="0">Abstract</a> | <a href="https://kar.kent.ac.uk/58396" title="View in KAR">View in KAR</a>        </p>
									<div class="KARAbstract panel panel-secondary" style="display: none;">The duration that exercise can be maintained decreases as the power requirements increase.  In this review we describe the power-duration (PD) relationship across the full range of attainable power outputs in humans.  We show that a remarkably small range of power outputs are sustainable (power outputs below the critical power, CP).  We also show that the origin of neuromuscular fatigue differs considerably depending on the exercise intensity domain in which exercise is performed.  In the moderate domain (below the lactate threshold, LT), fatigue develops slowly and is predominantly of central origin (residing in the central nervous system).  In the heavy domain (above LT but below CP), both central and peripheral (muscle) fatigue are observed.  In this domain, fatigue is frequently correlated with the depletion of muscle glycogen.  Severe-intensity exercise (above the CP) is associated with progressive derangements of muscle metabolic homeostasis and consequent peripheral fatigue.  To counter these effects, muscle activity increases progressively, as does pulmonary oxygen uptake (VO2), with task failure being associated with the attainment of VO2max.  Although the loss of homeostasis and thus fatigue develop more rapidly the higher the power output is above CP, the metabolic disturbance and the degree of peripheral fatigue reach similar values at task failure.  We provide evidence that the failure to continue severe-intensity exercise is a physiological phenomenon involving multiple interacting mechanisms which indicate a mismatch between neuromuscular power demand and instantaneous power supply. Valid integrative models of fatigue must account for the PD relationship and its physiological basis.</div>    </div>
							</li><li class="KARItem">

								<div class="KARPub">
									<!--<a title="view publication details in KAR" target="_self" href="http://dx.doi.org/10.1152/ajpregu.00019.2016">Loss of knee extensor torque complexity during fatiguing isometric muscle contractions occurs exclusively above the critical torque</a> -->
								<div class="csl-bib-body"><div style="text-indent: -25px; padding-left: 25px;"><div class="csl-entry">Pethick, J., Winter, S. and Burnley, M. (2016).<span class="citeproc-title"> Loss of knee extensor torque complexity during fatiguing isometric muscle contractions occurs exclusively above the critical torque</span>. <span class="citeproc-container-title"><span style="font-style: italic;">American Journal of Physiology-Regulatory Integrative and Comparative Physiology</span></span> <span class="citeproc-online">[Online]</span><span class="citeproc-volume"> <span style="font-weight: bold;">310</span></span><span class="citeproc-page">:R1144-R1153</span>. <span class="citeproc-available at">Available at</span>: <span class="citeproc-URL">http://dx.doi.org/10.1152/ajpregu.00019.2016</span>.</div></div></div>    </div>
								<div class="KARLinks" style="padding-left: 25px;">
									<p><a title="click to view/hide the abstract" class="KARToggleAbstract" tabindex="0">Abstract</a> | <a href="https://kar.kent.ac.uk/55011" title="View in KAR">View in KAR</a> | <span class="KARPdfIcon"><a href="http://kar.kent.ac.uk/55011/1/Article%20in%20press%20version.pdf" title="click here to open the full-text of this publication in a new window" target="_blank">View Full Text</a></span>        </p>
									<div class="KARAbstract panel panel-secondary" style="display: none;">The complexity of knee extensor torque time series decreases during fatiguing isometric muscle contractions. We hypothesised that, due to peripheral fatigue, this loss of torque complexity would occur exclusively during contractions above the critical torque (CT).  Nine healthy participants performed isometric knee extension exercise (6 s contraction, 4 s rest) on 6 occasions for 30 min or to task failure, whichever occurred sooner.  Four trials were performed above CT (trials S1-S4, S1 being the lowest intensity), and two were performed below CT (at 50% and 90% of CT).  Global, central and peripheral fatigue were quantified using maximal voluntary contractions (MVCs) with femoral nerve stimulation.  The complexity of torque output was determined using approximate entropy (ApEn) and the Detrended Fluctuation Analysis α scaling exponent (DFA α).  The MVC torque was reduced in trials below CT (by [Mean ± SEM] 19 ± 4% in 90%CT), but complexity did not decrease (ApEn for 90%CT: from 0.82 ± 0.03 to 0.75 ± 0.06, 95% paired-samples confidence intervals, 95% CI = –0.23, 0.10; DFA α from 1.36 ± 0.01 to 1.32 ± 0.03, 95% CI –0.12, 0.04).  Above CT, substantial reductions in MVC torque occurred (of 49 ± 8% in S1), and torque complexity was reduced (ApEn for S1: from 0.67 ± 0.06 to 0.14 ± 0.01, 95% CI = –0.72, –0.33; DFA α from 1.38 ± 0.03 to 1.58 ± 0.01, 95% CI 0.12, 0.29).  Thus, in these experiments, the fatigue-induced loss of torque complexity occurred exclusively during contractions performed above the CT.</div>    </div>
							</li></ul>
					</div>


					<div class="KARFooter">
						<div class="KARTotal">Showing 3 of 11 total publications in KAR. [<a :href="karLink">See all in KAR</a>]</div>
						<div class="KARLink">Information obtained from the <a href="http://kar.kent.ac.uk/">Kent Academic Repository</a>. The University of Kent official repository of academic activity.</div>
						<div class="KARLink">More information about the <a href="http://www.kent.ac.uk/library/staff/kar.html">Kent Academic Repository</a></div>
					</div>
				</section>
			</div>


			<aside v-if="profileData.location || profileData.office_hours" class="content content--aside content--aside-bottom">
				<ul class="info-sidebar">
					<li v-if="profileData.location">
						<i class="info-sidebar__icon kf-pin"></i><h3 class="info-sidebar__title">Location</h3>
						<ul class="info-sidebar__section">
							<li class="info-sidebar__item">
								<pre>{{ profileData.location }}</pre>
							</li>
						</ul>
					</li>

					<li v-if="profileData.office_hours">
						<i class="info-sidebar__icon kf-clock"></i><h3 class="info-sidebar__title">Office Hours</h3>
						<ul class="info-sidebar__section">
							<li class="info-sidebar__item">
								<pre>{{ profileData.office_hours }}</pre>
							</li>
						</ul>
					</li>
				</ul>
			</aside>

		</div>
	</section>
	</div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
	name: 'profile-preview',

	props: ['site-id', 'profile-id'],

	created() {
		this.loadProfileData();
	},

	computed: {
		...mapState({
			loading: state => state.profile.loading,
			profileData: state => state.profile.profileData
		}),

		name() {
			var name = '';
			if (this.profileData.title) name += this.profileData.title + ' ';
			if (this.profileData.first_name) name += this.profileData.first_name + ' ';
			if (this.profileData.last_name) name += this.profileData.last_name + ' ';
			return name;
		},

		// TODO get kar url from profile data when avaliable 
		karLink() {
			let kar_root = 'https://kar.kent.ac.uk/view/email/';
			return kar_root + 'this is made up' + '.html';
		},

		// do we have publications
		publications() {
			return true;
		}
	},

	methods: {
		...mapActions({
			fetchProfileData: 'profile/fetchProfileData'
		}),

		loadProfileData() {
			this.fetchProfileData({
				siteId: this.siteId,
				profileId: this.profileId
			})
		}
	}

};
</script>
