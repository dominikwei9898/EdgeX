/**
 * EverShop Gutenberg Blocks - Editor Script
 */

(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { InspectorControls, MediaUpload, RichText, URLInput } = wp.blockEditor;
    const { PanelBody, Button, TextControl, TextareaControl, SelectControl, ToggleControl, RangeControl, IconButton } = wp.components;
    const { Fragment } = wp.element;
    const { __ } = wp.i18n;

    // 注册区块分类
    wp.blocks.registerBlockCollection('evershop', {
        title: 'EverShop',
        icon: 'cart'
    });

    /**
     * 1. Key Benefits Block
     */
    registerBlockType('evershop/key-benefits', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { benefits, columns, alignment } = attributes;

            const addBenefit = () => {
                const newBenefits = [...benefits, { icon: '', title: '', description: '' }];
                setAttributes({ benefits: newBenefits });
            };

            const updateBenefit = (index, field, value) => {
                const newBenefits = [...benefits];
                newBenefits[index][field] = value;
                setAttributes({ benefits: newBenefits });
            };

            const removeBenefit = (index) => {
                const newBenefits = benefits.filter((_, i) => i !== index);
                setAttributes({ benefits: newBenefits });
            };

            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={__('Settings', 'evershop-integration')}>
                            <RangeControl
                                label={__('Columns', 'evershop-integration')}
                                value={columns}
                                onChange={(value) => setAttributes({ columns: value })}
                                min={1}
                                max={4}
                            />
                            <SelectControl
                                label={__('Alignment', 'evershop-integration')}
                                value={alignment}
                                options={[
                                    { label: 'Left', value: 'left' },
                                    { label: 'Center', value: 'center' },
                                    { label: 'Right', value: 'right' }
                                ]}
                                onChange={(value) => setAttributes({ alignment: value })}
                            />
                        </PanelBody>
                    </InspectorControls>

                    <div className="evershop-key-benefits-editor" style={{ textAlign: alignment }}>
                        <h3>🎯 Key Benefits</h3>
                        <div className="benefits-grid" style={{ display: 'grid', gridTemplateColumns: `repeat(${columns}, 1fr)`, gap: '20px' }}>
                            {benefits.map((benefit, index) => (
                                <div key={index} className="benefit-card" style={{ padding: '20px', border: '1px solid #ddd', borderRadius: '8px' }}>
                                    <TextControl
                                        label="Icon/Emoji"
                                        value={benefit.icon}
                                        onChange={(value) => updateBenefit(index, 'icon', value)}
                                        placeholder="e.g., 💪"
                                    />
                                    <TextControl
                                        label="Title"
                                        value={benefit.title}
                                        onChange={(value) => updateBenefit(index, 'title', value)}
                                        placeholder="Benefit Title"
                                    />
                                    <TextareaControl
                                        label="Description"
                                        value={benefit.description}
                                        onChange={(value) => updateBenefit(index, 'description', value)}
                                        placeholder="Describe the benefit..."
                                    />
                                    <Button
                                        isDestructive
                                        onClick={() => removeBenefit(index)}
                                    >
                                        Remove
                                    </Button>
                                </div>
                            ))}
                        </div>
                        <Button isPrimary onClick={addBenefit} style={{ marginTop: '20px' }}>
                            + Add Benefit
                        </Button>
                    </div>
                </Fragment>
            );
        },

        save: function() {
            return null; // Server-side rendering
        }
    });

    /**
     * 2. Product Videos Block
     */
    registerBlockType('evershop/product-videos', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { title, videos, autoplay } = attributes;

            const addVideo = () => {
                const newVideos = [...videos, { url: '', title: '' }];
                setAttributes({ videos: newVideos });
            };

            const updateVideo = (index, field, value) => {
                const newVideos = [...videos];
                newVideos[index][field] = value;
                setAttributes({ videos: newVideos });
            };

            const removeVideo = (index) => {
                const newVideos = videos.filter((_, i) => i !== index);
                setAttributes({ videos: newVideos });
            };

            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={__('Settings', 'evershop-integration')}>
                            <ToggleControl
                                label={__('Autoplay', 'evershop-integration')}
                                checked={autoplay}
                                onChange={(value) => setAttributes({ autoplay: value })}
                            />
                        </PanelBody>
                    </InspectorControls>

                    <div className="evershop-product-videos-editor">
                        <TextControl
                            label="Section Title"
                            value={title}
                            onChange={(value) => setAttributes({ title: value })}
                            placeholder="Product Videos"
                        />
                        <div className="videos-list" style={{ marginTop: '20px' }}>
                            {videos.map((video, index) => (
                                <div key={index} style={{ marginBottom: '15px', padding: '15px', border: '1px solid #ddd', borderRadius: '4px' }}>
                                    <TextControl
                                        label={`Video ${index + 1} URL`}
                                        value={video.url}
                                        onChange={(value) => updateVideo(index, 'url', value)}
                                        placeholder="https://www.youtube.com/watch?v=..."
                                    />
                                    <TextControl
                                        label="Video Title"
                                        value={video.title}
                                        onChange={(value) => updateVideo(index, 'title', value)}
                                        placeholder="Optional title"
                                    />
                                    <Button isDestructive onClick={() => removeVideo(index)}>
                                        Remove Video
                                    </Button>
                                </div>
                            ))}
                        </div>
                        <Button isPrimary onClick={addVideo}>
                            + Add Video
                        </Button>
                    </div>
                </Fragment>
            );
        },

        save: function() {
            return null;
        }
    });

    /**
     * 3. Testimonials Block
     */
    registerBlockType('evershop/testimonials', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { title, testimonials, autoplay, delay } = attributes;

            const addTestimonial = () => {
                const newTestimonials = [...testimonials, { author: '', rating: 5, content: '', role: '', avatar: '' }];
                setAttributes({ testimonials: newTestimonials });
            };

            const updateTestimonial = (index, field, value) => {
                const newTestimonials = [...testimonials];
                newTestimonials[index][field] = value;
                setAttributes({ testimonials: newTestimonials });
            };

            const removeTestimonial = (index) => {
                const newTestimonials = testimonials.filter((_, i) => i !== index);
                setAttributes({ testimonials: newTestimonials });
            };

            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={__('Settings', 'evershop-integration')}>
                            <ToggleControl
                                label={__('Autoplay', 'evershop-integration')}
                                checked={autoplay}
                                onChange={(value) => setAttributes({ autoplay: value })}
                            />
                            <RangeControl
                                label={__('Autoplay Delay (ms)', 'evershop-integration')}
                                value={delay}
                                onChange={(value) => setAttributes({ delay: value })}
                                min={2000}
                                max={10000}
                                step={500}
                            />
                        </PanelBody>
                    </InspectorControls>

                    <div className="evershop-testimonials-editor">
                        <TextControl
                            label="Section Title"
                            value={title}
                            onChange={(value) => setAttributes({ title: value })}
                            placeholder="Customer Reviews"
                        />
                        <div className="testimonials-list" style={{ marginTop: '20px' }}>
                            {testimonials.map((testimonial, index) => (
                                <div key={index} style={{ marginBottom: '15px', padding: '15px', border: '1px solid #ddd', borderRadius: '4px' }}>
                                    <TextControl
                                        label="Author Name"
                                        value={testimonial.author}
                                        onChange={(value) => updateTestimonial(index, 'author', value)}
                                        placeholder="John Doe"
                                    />
                                    <RangeControl
                                        label="Rating"
                                        value={testimonial.rating}
                                        onChange={(value) => updateTestimonial(index, 'rating', value)}
                                        min={1}
                                        max={5}
                                    />
                                    <TextareaControl
                                        label="Review Content"
                                        value={testimonial.content}
                                        onChange={(value) => updateTestimonial(index, 'content', value)}
                                        placeholder="Amazing product!"
                                    />
                                    <TextControl
                                        label="Role/Title"
                                        value={testimonial.role}
                                        onChange={(value) => updateTestimonial(index, 'role', value)}
                                        placeholder="Professional Athlete"
                                    />
                                    <TextControl
                                        label="Avatar URL"
                                        value={testimonial.avatar}
                                        onChange={(value) => updateTestimonial(index, 'avatar', value)}
                                        placeholder="https://..."
                                    />
                                    <Button isDestructive onClick={() => removeTestimonial(index)}>
                                        Remove Testimonial
                                    </Button>
                                </div>
                            ))}
                        </div>
                        <Button isPrimary onClick={addTestimonial}>
                            + Add Testimonial
                        </Button>
                    </div>
                </Fragment>
            );
        },

        save: function() {
            return null;
        }
    });

    /**
     * 4. Image + Text Block
     */
    registerBlockType('evershop/image-text', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { imageUrl, imagePosition, title, content, buttonText, buttonUrl } = attributes;

            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={__('Settings', 'evershop-integration')}>
                            <SelectControl
                                label={__('Image Position', 'evershop-integration')}
                                value={imagePosition}
                                options={[
                                    { label: 'Left', value: 'left' },
                                    { label: 'Right', value: 'right' }
                                ]}
                                onChange={(value) => setAttributes({ imagePosition: value })}
                            />
                        </PanelBody>
                    </InspectorControls>

                    <div className="evershop-image-text-editor" style={{ display: 'flex', flexDirection: imagePosition === 'right' ? 'row-reverse' : 'row', gap: '20px', alignItems: 'center' }}>
                        <div className="image-section" style={{ flex: 1 }}>
                            {imageUrl ? (
                                <div>
                                    <img src={imageUrl} alt={title} style={{ maxWidth: '100%', height: 'auto' }} />
                                    <Button
                                        onClick={() => setAttributes({ imageUrl: '' })}
                                        isDestructive
                                        style={{ marginTop: '10px' }}
                                    >
                                        Remove Image
                                    </Button>
                                </div>
                            ) : (
                                <MediaUpload
                                    onSelect={(media) => setAttributes({ imageUrl: media.url })}
                                    type="image"
                                    render={({ open }) => (
                                        <Button onClick={open} isPrimary>
                                            Upload Image
                                        </Button>
                                    )}
                                />
                            )}
                        </div>
                        <div className="text-section" style={{ flex: 1 }}>
                            <TextControl
                                label="Title"
                                value={title}
                                onChange={(value) => setAttributes({ title: value })}
                                placeholder="Section Title"
                            />
                            <TextareaControl
                                label="Content"
                                value={content}
                                onChange={(value) => setAttributes({ content: value })}
                                placeholder="Write your content here..."
                                rows={5}
                            />
                            <TextControl
                                label="Button Text"
                                value={buttonText}
                                onChange={(value) => setAttributes({ buttonText: value })}
                                placeholder="Learn More"
                            />
                            <TextControl
                                label="Button URL"
                                value={buttonUrl}
                                onChange={(value) => setAttributes({ buttonUrl: value })}
                                placeholder="https://..."
                            />
                        </div>
                    </div>
                </Fragment>
            );
        },

        save: function() {
            return null;
        }
    });

    /**
     * 5. Trust Badges Block
     */
    registerBlockType('evershop/trust-badges', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { badges, layout } = attributes;

            const addBadge = () => {
                const newBadges = [...badges, { icon: '', title: '', description: '' }];
                setAttributes({ badges: newBadges });
            };

            const updateBadge = (index, field, value) => {
                const newBadges = [...badges];
                newBadges[index][field] = value;
                setAttributes({ badges: newBadges });
            };

            const removeBadge = (index) => {
                const newBadges = badges.filter((_, i) => i !== index);
                setAttributes({ badges: newBadges });
            };

            return (
                <Fragment>
                    <InspectorControls>
                        <PanelBody title={__('Settings', 'evershop-integration')}>
                            <SelectControl
                                label={__('Layout', 'evershop-integration')}
                                value={layout}
                                options={[
                                    { label: 'Horizontal', value: 'horizontal' },
                                    { label: 'Vertical', value: 'vertical' }
                                ]}
                                onChange={(value) => setAttributes({ layout: value })}
                            />
                        </PanelBody>
                    </InspectorControls>

                    <div className="evershop-trust-badges-editor">
                        <h3>🛡️ Trust Badges</h3>
                        <div className="badges-list" style={{ display: 'flex', flexDirection: layout === 'vertical' ? 'column' : 'row', gap: '15px', marginTop: '20px' }}>
                            {badges.map((badge, index) => (
                                <div key={index} style={{ padding: '15px', border: '1px solid #ddd', borderRadius: '4px', flex: layout === 'horizontal' ? 1 : 'auto' }}>
                                    <TextControl
                                        label="Icon/Emoji or URL"
                                        value={badge.icon}
                                        onChange={(value) => updateBadge(index, 'icon', value)}
                                        placeholder="🔒 or https://..."
                                    />
                                    <TextControl
                                        label="Title"
                                        value={badge.title}
                                        onChange={(value) => updateBadge(index, 'title', value)}
                                        placeholder="Secure Payment"
                                    />
                                    <TextControl
                                        label="Description"
                                        value={badge.description}
                                        onChange={(value) => updateBadge(index, 'description', value)}
                                        placeholder="100% secure transactions"
                                    />
                                    <Button isDestructive onClick={() => removeBadge(index)}>
                                        Remove
                                    </Button>
                                </div>
                            ))}
                        </div>
                        <Button isPrimary onClick={addBadge} style={{ marginTop: '15px' }}>
                            + Add Badge
                        </Button>
                    </div>
                </Fragment>
            );
        },

        save: function() {
            return null;
        }
    });

    /**
     * 6. Custom HTML Block
     */
    registerBlockType('evershop/custom-html', {
        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { content, className } = attributes;

            return (
                <div className="evershop-custom-html-editor">
                    <h3>📝 Custom HTML</h3>
                    <TextControl
                        label="CSS Class (optional)"
                        value={className}
                        onChange={(value) => setAttributes({ className: value })}
                        placeholder="my-custom-class"
                    />
                    <TextareaControl
                        label="HTML Content"
                        value={content}
                        onChange={(value) => setAttributes({ content: value })}
                        placeholder="<div>Your HTML here...</div>"
                        rows={10}
                        help="Add your custom HTML code. Use with caution."
                    />
                    <div style={{ marginTop: '20px', padding: '15px', background: '#f0f0f0', borderRadius: '4px' }}>
                        <strong>Preview:</strong>
                        <div dangerouslySetInnerHTML={{ __html: content }}></div>
                    </div>
                </div>
            );
        },

        save: function() {
            return null;
        }
    });

})(window.wp);

