<!-- EMAIL FEATURED TEMPLATE -->  
        <table border="0" cellpadding="0" cellspacing="0" class="columns-container">
					<tr>
						<td class="force-col" style="padding-right: 20px;" valign="top">
							<!-- ### COLUMN 1 ### -->
							<table border="0" cellspacing="0" cellpadding="0" width="324" align="left" class="featured">
								<tr>
									<td align="left" valign="top" style="font-size:28px; line-height: 32px; font-family: Arial, sans-serif; padding-bottom: 30px;">
										<br>
										<!-- Start Featured Article Title -->
										<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" style="font-weight:bold"><?php the_title(); ?></a>
										<!-- End Featured Article Title -->
										<br><br>
										<!-- Start Featured Article Button -->
										<a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" style=" line-height: 16px; font-size: 16px; font-style: italic; font-family: Arial, sans-serif; text-decoration: none; border: 2px solid; padding: 8px; border-radius: 3px;">Read this article</a>
										<!-- End Featured Article Title -->
										<br>
									</td>
								</tr>
							</table>
						</td>
						<td class="force-col"  valign="top">
							<!-- ### COLUMN 2 ### -->
							<table border="0" cellspacing="0" cellpadding="0" width="324" align="right" class="featured" id="featured-last">
								<tr>
									<td align="left" valign="top" style="font-size:13px; line-height: 20px; font-family: Arial, sans-serif;">
										<!-- Start Featured Article Image -->
										<a href="<?php the_permalink(); ?>"><img src="<?php echo $thumb; ?>" alt="<?php echo $thumb['alt']; ?>" border="0" hspace="0" vspace="0" style="vertical-align:top; max-width: 324px;" class="emailimg"></a>
										<!-- End Featured Article Image -->
										<br>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table><!--/ end .columns-container-->



<!-- EMAIL STORY LIST TEMPLATE -->

<tr>
    <td align="left" valign="top" style="font-size:22px; line-height: 26px; font-family: Arial, sans-serif; padding-bottom: 30px;">
        <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" style="font-weight:bold; font-size:22px; line-height: 26px; font-family: Arial, sans-serif;">
            <?php the_title(); ?>
        </a>
        <br>										
    </td>
</tr>