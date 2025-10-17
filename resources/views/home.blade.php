@extends('layouts.app')

@section('title', 'ReCircle - Circular Economy Marketplace')
@section('meta_description', 'A marketplace where households and companies list reusable waste, local makers bid to transform it, and we track environmental impact of every transaction.')

@section('content')
    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title">Connect. Transform. Impact.</h1>
                <p class="hero-subtitle">A marketplace where households and companies list reusable waste, local makers bid to transform it into sellable goods, while we track the environmental impact of every transaction.</p>
                <div class="hero-buttons">
                    <a href="#how-it-works" class="btn btn-primary">See How It Works</a>
                    <a href="{{ route('auth') }}" class="btn btn-secondary">Join the Platform</a>
                    <a href="{{ route('reclamations.create') }}" class="btn btn-primary" style="background:#ff6b4a; border-color:transparent;">Report an Issue</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="circular-graphic">
                    <div class="circle circle-1"></div>
                    <div class="circle circle-2"></div>
                    <div class="circle circle-3"></div>
                    <div class="recycle-icon"><i class="fa-solid fa-recycle" aria-hidden="true"></i></div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="how-it-works">
        <div class="container">
            <div class="section-header">
                <h2>How It Works</h2>
                <p>From waste listing to impact tracking - see the complete journey</p>
            </div>
            <div class="journey-steps">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>List Your Waste</h3>
                        <p>Generators snap photos and create listings with auto-suggested material types and weights</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Makers Bid</h3>
                        <p>Local makers and repairers bid with their price and timeline proposals</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Match & Pickup</h3>
                        <p>Generator accepts a bid, system books courier pickup, and work order begins</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Transform & Track</h3>
                        <p>Makers follow process steps while we generate material passports for traceability</p>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">5</div>
                    <div class="step-content">
                        <h3>Sell & Impact</h3>
                        <p>Products go live on marketplace while we track CO₂ saved and landfill diverted</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section id="roles" class="roles">
        <div class="container">
            <div class="section-header">
                <h2>Platform Roles</h2>
                <p>Everyone has a part to play in the circular economy</p>
            </div>
            <div class="roles-grid">
                <div class="role-item">
                    <div class="role-icon"><i class="fa-solid fa-house text-3xl text-green-600" aria-hidden="true"></i></div>
                    <div class="role-content">
                        <h3>Generator</h3>
                        <p class="role-type">Households & Companies</p>
                        <p>List reusable waste like pallets, fabric scraps, or broken electronics for transformation</p>
                    </div>
                </div>
                <div class="role-item">
                    <div class="role-icon"><i class="fa-solid fa-hammer text-3xl text-green-600" aria-hidden="true"></i></div>
                    <div class="role-content">
                        <h3>Maker/Repairer</h3>
                        <p class="role-type">Local Artisans</p>
                        <p>Bid on listings and execute upcycling or repair work to create sellable goods</p>
                    </div>
                </div>
                <div class="role-item">
                    <div class="role-icon"><i class="fa-solid fa-cart-shopping text-3xl text-green-600" aria-hidden="true"></i></div>
                    <div class="role-content">
                        <h3>Buyer</h3>
                        <p class="role-type">NGOs, Schools & Individuals</p>
                        <p>Purchase or request donations of transformed products from the marketplace</p>
                    </div>
                </div>
                <div class="role-item">
                    <div class="role-icon"><i class="fa-solid fa-truck text-3xl text-green-600" aria-hidden="true"></i></div>
                    <div class="role-content">
                        <h3>Courier</h3>
                        <p class="role-type">Logistics Partners</p>
                        <p>Handle pickups from generators and deliveries to makers and final customers</p>
                    </div>
                </div>
                <div class="role-item">
                    <div class="role-icon"><i class="fa-solid fa-gear text-3xl text-green-600" aria-hidden="true"></i></div>
                    <div class="role-content">
                        <h3>Admin</h3>
                        <p class="role-type">Platform Management</p>
                        <p>Moderate listings, set pricing rules, and generate comprehensive impact reports</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Impact Section -->
    <section id="impact" class="impact">
        <div class="container">
            <div class="section-header">
                <h2>Our Impact</h2>
                <p>Real-time tracking of environmental benefits from every transaction</p>
            </div>
            <div class="impact-stats">
                <div class="stat-item">
                    <div class="stat-number" data-target="12500">0</div>
                    <div class="stat-label">Kg CO₂ Saved</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="8750">0</div>
                    <div class="stat-label">Kg Landfill Diverted</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="1240">0</div>
                    <div class="stat-label">Active Listings</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-target="3200">0</div>
                    <div class="stat-label">Products Created</div>
                </div>
            </div>
            <div class="impact-content">
                <div class="impact-text">
                    <h3>Transparent Impact Tracking</h3>
                    <p>Every product on our marketplace comes with a Material Passport showing its transformation journey and environmental impact. We track CO₂ savings and landfill diversion in real-time, making sustainability measurable and transparent.</p>
                    <ul class="impact-list">
                        <li>Material Passports for complete traceability</li>
                        <li>Real-time CO₂ and landfill impact calculations</li>
                        <li>Public dashboards showing environmental benefits</li>
                        <li>Automated logistics and payment processing</li>
                    </ul>
                </div>
                <div class="impact-visual">
                    <div class="progress-circle">
                        <div class="progress-text">
                            <span class="progress-number">92%</span>
                            <span class="progress-label">Success Rate</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2>Get Involved</h2>
                <p>Ready to join the circular economy marketplace?</p>
            </div>
            <div class="contact-content">
                <div class="contact-info">
                    <h3>Join Our Platform</h3>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fa-solid fa-house text-xl text-green-600" aria-hidden="true"></i></div>
                        <div>
                            <h4>For Generators</h4>
                            <p>List your reusable waste and connect with local makers</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fa-solid fa-hammer text-xl text-green-600" aria-hidden="true"></i></div>
                        <div>
                            <h4>For Makers</h4>
                            <p>Bid on materials and showcase your transformation skills</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fa-solid fa-cart-shopping text-xl text-green-600" aria-hidden="true"></i></div>
                        <div>
                            <h4>For Buyers</h4>
                            <p>Discover unique upcycled products with impact stories</p>
                        </div>
                    </div>
                </div>
                <form class="contact-form">
                    <div class="form-group">
                        <input type="text" id="name" name="name" placeholder="Your Name" required>
                    </div>
                    <div class="form-group">
                        <input type="email" id="email" name="email" placeholder="Your Email" required>
                    </div>
                    <div class="form-group">
                        <select id="interest" name="interest" required>
                            <option value="">Select Your Role</option>
                            <option value="generator">Generator (List Waste)</option>
                            <option value="maker">Maker/Repairer</option>
                            <option value="buyer">Buyer</option>
                            <option value="courier">Courier Partner</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea id="message" name="message" placeholder="Your Message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </section>
@endsection
