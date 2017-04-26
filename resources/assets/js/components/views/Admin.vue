<template>
<div class="admin-wrapper">
	<aside class="left-side sidebar-offcanvas">
		<section class="sidebar">
			<ul class="app-sidebar" role="navigation">
				<li>
					<SideMenuItem
						link="/home"
						:icon="homeIcon"
						title="Home"
					/>
				</li>
				<li>
					<SideMenuItem
						link="/sites"
						:icon="sitesIcon"
						title="Sites"
					/>
				</li>
				<li>
					<SideMenuItem
						link="/media"
						:icon="mediaIcon"
						title="Media Manager"
					/>
				</li>
				<li>
					<SideMenuItem
						link="/settings"
						:icon="settingsIcon"
						title="General Settings"
					/>
				</li>
				<!-- <li class="menu-dropdown">
					<a href="#">
						<i class="menu-icon ti-menu"></i>
						<span>
								Menu levels
							</span>
						<span class="fa arrow"></span>
					</a>
					<ul class="sub-menu collapse">
						<li>
							<a href="#">
								<i class="fa fa-fw ti-control-skip-forward"></i> Level 1
								<span class="fa arrow"></span>
							</a>
							<ul class="sub-menu sub-submenu collapse">
								<li>
									<a href="#">
										<i class="fa fa-fw ti-control-skip-forward"></i> Level 2
									</a>
								</li>
								<li>
									<a href="#">
										<i class="fa fa-fw ti-control-skip-forward"></i> Level 2
									</a>
								</li>
								<li>
									<a href="#">
										<i class="fa fa-fw ti-control-skip-forward"></i> Level 2
										<span class="fa arrow"></span>
									</a>
									<ul class="sub-menu sub-submenu collapse">
										<li>
											<a href="#">
												<i class="fa fa-fw ti-control-skip-forward"></i> Level 3
											</a>
										</li>
										<li>
											<a href="#">
												<i class="fa fa-fw ti-control-skip-forward"></i> Level 3
											</a>
										</li>
										<li>
											<a href="#">
												<i class="fa fa-fw ti-control-skip-forward"></i> Level 3
												<span class="fa arrow"></span>
											</a>
											<ul class="sub-menu sub-submenu collapse">
												<li>
													<a href="#">
														<i class="fa fa-fw ti-control-skip-forward"></i> Level 4
													</a>
												</li>
												<li>
													<a href="#">
														<i class="fa fa-fw ti-control-skip-forward"></i> Level 4
													</a>
												</li>
												<li>
													<a href="#">
														<i class="fa fa-fw ti-control-skip-forward"></i> Level 4
														<span class="fa arrow"></span>
													</a>
													<ul class="sub-menu sub-submenu collapse">
														<li>
															<a href="#">
																<i class="fa fa-fw ti-control-skip-forward"></i> Level 5
															</a>
														</li>
														<li>
															<a href="#">
																<i class="fa fa-fw ti-control-skip-forward"></i> Level 5
															</a>
														</li>
														<li>
															<a href="#">
																<i class="fa fa-fw ti-control-skip-forward"></i> Level 5
															</a>
														</li>
													</ul>
												</li>
											</ul>
										</li>
										<li>
											<a href="#">
												<i class="fa fa-fw ti-control-skip-forward"></i> Level 4
											</a>
										</li>
									</ul>
								</li>
								<li>
									<a href="#">
										<i class="fa fa-fw ti-control-skip-forward"></i> Level 2
										<span class="fa arrow"></span>
									</a>
								</li>
							</ul>
						</li>
						<li>
							<a href="#">
								<i class="fa fa-fw ti-control-skip-forward"></i> Level 1
							</a>
						</li>
						<li>
							<a href="#">
								<i class="fa fa-fw ti-control-skip-forward"></i> Level 1
								<span class="fa arrow"></span>
							</a>
							<ul class="sub-menu sub-submenu collapse">
								<li>
									<a href="#">
										<i class="fa fa-fw ti-control-skip-forward"></i> Level 2
										<span class="fa arrow"></span>
									</a>
								</li>
								<li>
									<a href="#">
										<i class="fa fa-fw ti-control-skip-forward"></i> Level 2
										<span class="fa arrow"></span>
									</a>
								</li>
								<li>
									<a href="#">
										<i class="fa fa-fw ti-control-skip-forward"></i> Level 2
										<span class="fa arrow"></span>
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</li> -->
			</ul>
		</section>
	</aside>
	<div class="router-wrapper">
		<router-view />
	</div>
</div>
</template>

<script>
import Icon from '../Icon.vue';
import homeIcon from '!IconPath/home.svg';
import settingsIcon from '!IconPath/settings.svg';
import sitesIcon from '!IconPath/files.svg';
import mediaIcon from '!IconPath/agenda.svg';

export default {
	name: 'Admin',

	components: {
		SideMenuItem: {
			props: ['link', 'icon', 'title'],

			components: {
				Icon
			},

			template: `
				<router-link :to="link">
					<Icon :glyph="icon" className="menu-icon" />
					<span>{{ title }}</span>
				</router-link>
			`
		}
	},

	data() {
		return {
			homeIcon,
			settingsIcon,
			sitesIcon,
			mediaIcon
		};
	}
};
</script>